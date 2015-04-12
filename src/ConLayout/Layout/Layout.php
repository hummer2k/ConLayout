<?php

namespace ConLayout\Layout;

use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
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
     * flag if blocks have already been removed from layout structure
     *
     * @var bool
     */
    protected $blocksRemoved = false;

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
     * @var ModelInterface[]
     */
    protected $blocksByParent = [];

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
     *
     * @param   BlockFactoryInterface   $blockFactory
     * @param   LayoutUpdaterInterface  $updater
     */
    public function __construct(
        BlockFactoryInterface $blockFactory,
        LayoutUpdaterInterface $updater
    )
    {
        $this->blockFactory = $blockFactory;
        $this->updater = $updater;
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
                if ($this->isBlockRemoved($blockId)) continue;
                $this->addBlock($blockId, $this->blockFactory->createBlock($blockId, $specs));
            }
            $this->sortBlocks();
            $this->blocksGenerated = true;
        }
    }

    protected function removeBlocksFromStructure()
    {
        if (!$this->blocksRemoved) {
            $removedBlocks = $this->updater->getLayoutStructure()
                ->get(LayoutUpdaterInterface::INSTRUCTION_REMOVE_BLOCKS, []);
            if ($removedBlocks instanceof Config) {
                $removedBlocks = $removedBlocks->toArray();
                foreach (array_keys($removedBlocks) as $removedBlockId) {
                    $this->removeBlock($removedBlockId);
                }
            }
            $this->blocksRemoved = true;
        }
    }

    /**
     * check if block has been removed
     *
     * @param   string  $blockId
     * @return  boolean
     */
    protected function isBlockRemoved($blockId)
    {
        $this->removeBlocksFromStructure();
        return isset($this->removedBlocks[$blockId]);
    }

    /**
     * sort the blocks by order option ascendant
     * 
     * @return LayoutManagerInterface
     */
    protected function sortBlocks()
    {
        uasort($this->blocks, function($a, $b) {
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
    public function injectBlocks(ModelInterface $root = null)
    {
        if (null !== $root) {
            $this->addBlock(self::BLOCK_NAME_ROOT, $root);
        }
        foreach ($this->getBlocks() as $blockId => $block) {
            if (!$this->isAllowed($blockId, $block)) continue;
            list($parent, $captureTo) = $this->getCaptureTo($block);
            if ($parentBlock = $this->getBlock($parent)) {
                $parentBlock->addChild($block, $captureTo);
            }
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
        if ($blockId === self::BLOCK_NAME_ROOT) {
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
    public function addBlock($blockId, ViewModel $block, $parentId = LayoutInterface::BLOCK_NAME_ROOT)
    {
        if ($block->hasChildren()) {
            foreach ($block->getChildren() as $childBlock) {
                $childBlockId = $this->determineAnonymousBlockId($childBlock);
                $childBlock->setCaptureTo(
                    $blockId . self::CAPTURE_TO_DELIMITER . $childBlock->captureTo()
                );
                $this->addBlock(
                    $childBlockId,
                    $childBlock
                );
            }
            $block->clearChildren();
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
        if (false !== strpos($captureTo, self::CAPTURE_TO_DELIMITER)) {
            return explode(self::CAPTURE_TO_DELIMITER, $captureTo);
        }
        return [
            self::BLOCK_NAME_ROOT,
            $captureTo
        ];
    }

    /**
     * retrieve single block by its id
     *
     * @param   string                  $blockId
     * @param   bool                    $recursive
     * @return  false|ModelInterface
     */
    public function getBlock($blockId, $recursive = false)
    {
        $this->generateBlocks();
        if (isset($this->blocks[$blockId])) {
            if (!$recursive) {
                return $this->blocks[$blockId];
            }
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
        $blockName = $block->getVariable(
            'nameInLayout',
            sprintf(
                'anonymous.%s.%s',
                $block->captureTo(),
                self::$anonymousSuffix++
            )
        );
        return $blockName;
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
                function() {
                    return true;
                },
                10000
            );
    }
}