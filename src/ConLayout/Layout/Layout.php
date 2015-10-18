<?php

namespace ConLayout\Layout;

use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\View\Model\ClearableModelInterface;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Layout implements
    EventManagerAwareInterface,
    LayoutInterface
{
    use EventManagerAwareTrait;

    const CAPTURE_TO_DELIMITER = '::';
    const ANONYMOUS_ID_PATTERN = 'anonymous.%s.%s';

    /**
     * suffix for anonymous block names
     * will be incremented on every addBlock call when block has no id
     *
     * @var int
     */
    protected static $anonymousSuffix = 1;

    /**
     * flag if blocks have already been generated
     *
     * @var bool
     */
    protected $blocksGenerated = false;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     * blocks registry
     *
     * @var ModelInterface[]
     */
    protected $blocks = [];

    /**
     *
     * @var array
     */
    protected $removedBlocks = [];

    /**
     *
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * flag if layout has been loaded
     *
     * @var bool
     */
    protected $isLoaded = false;

    /**
     *
     * @param   BlockFactoryInterface   $blockFactory
     * @param   LayoutUpdaterInterface  $updater
     */
    public function __construct(
        BlockFactoryInterface $blockFactory,
        LayoutUpdaterInterface $updater
    ) {
        $this->blockFactory = $blockFactory;
        $this->updater = $updater;
    }

    /**
     * set root view model/layout
     *
     * @param ModelInterface $root
     */
    public function setRoot(ModelInterface $root)
    {
        $root->setVariable(self::BLOCK_ID_VAR, self::BLOCK_ID_ROOT);
        $this->addBlock(self::BLOCK_ID_ROOT, $root);
    }

    /**
     * generate blocks from array configuration
     *
     * @param array $blockConfig
     */
    protected function generateBlocks()
    {
        if (false === $this->blocksGenerated) {
            $blocks = $this->updater->getLayoutStructure()
                ->get(LayoutUpdaterInterface::INSTRUCTION_BLOCKS, []);
            if ($blocks instanceof Config) {
                $blocks = $blocks->toArray();
            }
            foreach ($blocks as $blockId => $specs) {
                if ($this->isBlockRemoved($blockId, $specs)) {
                    continue;
                }
                $block = $this->blockFactory->createBlock($blockId, $specs);
                $this->addBlock($blockId, $block);
            }
            $this->blocksGenerated = true;
        }
    }

    /**
     * check if block has been removed
     *
     * @param   string $blockId
     * @param   array  $specs
     * @return  boolean
     */
    protected function isBlockRemoved($blockId, array $specs)
    {
        if (isset($this->removedBlocks[$blockId])) {
            return true;
        } elseif (isset($specs['remove'])) {
            $isRemoved = filter_var($specs['remove'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return (false !== $isRemoved);
        }
        return false;
    }

    /**
     * sort the blocks by order option ascendant
     *
     * @return LayoutManagerInterface
     */
    protected function sortBlocks()
    {
        foreach ($this->blocks as $block) {
            if ($beforeBlockId = $block->getOption('before')) {
                if ($beforeBlock = $this->getBlock($beforeBlockId)) {
                    $block->setOption('order', $beforeBlock->getOption('order', 0) - 1);
                }
            }
            if ($afterBlockId = $block->getOption('after')) {
                if ($afterBlock = $this->getBlock($afterBlockId)) {
                    $block->setOption('order', $afterBlock->getOption('order', 0) + 1);
                }
            }
        }
        uasort($this->blocks, function ($a, $b) {
            /* @var $a ModelInterface */
            /* @var $b ModelInterface */
            $orderA = $a->getOption('order', 0);
            $orderB = $b->getOption('order', 0);
            if ($orderA == $orderB) {
                return 0;
            }
            return ($orderA < $orderB) ? -1 : 1;
        });
        return $this;
    }

    /**
     * inject blocks into the root view model
     *
     * @param   ModelInterface  $root
     * @return  LayoutInterface
     */
    public function load()
    {
        if (false === $this->isLoaded) {
            $this->getEventManager()->trigger(
                __FUNCTION__ . '.pre',
                $this,
                []
            );
            $this->generateBlocks();
            $this->sortBlocks();
            foreach ($this->getBlocks() as $blockId => $block) {
                if (!$this->isAllowed($blockId, $block)) {
                    continue;
                }
                list($parent, $captureTo) = $this->getCaptureTo($block);
                if ($parentBlock = $this->getBlock($parent)) {
                    $parentBlock->addChild($block, $captureTo);
                    $block->setOption('parent_block', $parentBlock);
                }
            }
            $this->isLoaded = true;
            $this->getEventManager()->trigger(
                __FUNCTION__ . '.post',
                $this,
                []
            );
        }
        return $this;
    }

    /**
     * Determines whether a block should be allowed given certain parameters
     *
     * @param   string          $blockId
     * @param   ModelInterface  $block
     * @return  bool
     */
    protected function isAllowed($blockId, ModelInterface $block)
    {
        if ($blockId === self::BLOCK_ID_ROOT) {
            return false;
        }
        $results = $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            ['block' => $block, 'block_id' => $blockId]
        );
        $isAllowed = $results->last();
        return $isAllowed;
    }

    /**
     * adds a block to the registry
     *
     * @param   string          $blockId
     * @param   ModelInterface  $block
     * @return  LayoutInterface
     */
    public function addBlock($blockId, ModelInterface $block)
    {
        if ($block->hasChildren()) {
            foreach ($block->getChildren() as $childBlock) {
                $childBlockId = $this->determineAnonymousBlockId($childBlock);
                $childBlock->setCaptureTo(
                    $blockId . self::CAPTURE_TO_DELIMITER . $childBlock->captureTo()
                );
                $this->addBlock($childBlockId, $childBlock);
            }
            if ($block instanceof ClearableModelInterface) {
                $block->clearChildren();
            }
        }
        $this->blocks[$blockId] = $block;
        return $this;
    }

    /**
     * removes a single block from block registry
     *
     * @param   string          $blockId
     * @return  LayoutInterface
     */
    public function removeBlock($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
        $this->removedBlocks[$blockId] = true;
        return $this;
    }

    /**
     * retrieve parent and capture_to as array, e.g.: [ 'layout', 'content' ]
     * so we are able to list() block_id and capture_to values
     *
     * @param   ModelInterface  $block
     * @return  array
     */
    protected function getCaptureTo(ModelInterface $block)
    {
        $captureTo = $block->captureTo();
        if ($parent = $block->getOption('parent')) {
            $captureTo = explode(self::CAPTURE_TO_DELIMITER, $captureTo);
            return [
                $parent,
                end($captureTo)
            ];
        }
        if (false !== strpos($captureTo, self::CAPTURE_TO_DELIMITER)) {
            return explode(self::CAPTURE_TO_DELIMITER, $captureTo);
        }
        return [
            self::BLOCK_ID_ROOT,
            $captureTo
        ];
    }

    /**
     * retrieve single block by its id
     *
     * @param   string                  $blockId
     * @return  false|ModelInterface
     */
    public function getBlock($blockId)
    {
        $this->generateBlocks();
        if (isset($this->blocks[$blockId])) {
            return $this->blocks[$blockId];
        }
        return false;
    }

    /**
     * retrieve generated blocks
     * format:
     * [
     *     'block_id' => ModelInterface
     * ]
     *
     * @return ModelInterface[]
     */
    public function getBlocks()
    {
        $this->generateBlocks();
        return $this->blocks;
    }

    /**
     *
     * @param ViewModel $block
     * @return string
     */
    protected function determineAnonymousBlockId(ModelInterface $block)
    {
        $blockId = $block->getVariable(self::BLOCK_ID_VAR);
        if (!$blockId) {
            $blockId = sprintf(
                self::ANONYMOUS_ID_PATTERN,
                $block->captureTo(),
                self::$anonymousSuffix++
            );
            $block->setVariable(self::BLOCK_ID_VAR, $blockId);
        }
        return $blockId;
    }

    /**
     * attach default listeners: allows all blocks by default
     */
    protected function attachDefaultListeners()
    {
        $this->getEventManager()
            ->getSharedManager()
            ->attach(
                __CLASS__,
                'isAllowed',
                function () {
                    return true;
                },
                10000
            );
    }
}
