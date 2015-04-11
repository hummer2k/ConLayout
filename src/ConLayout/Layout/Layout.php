<?php

namespace ConLayout\Layout;

use ConLayout\Factory\BlockFactoryInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ModelInterface;

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
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     *
     * @param BlockFactoryInterface $blockFactory
     * @param LayoutUpdaterInterface $updater
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
     *
     * @param array $blockConfig
     */
    protected function generateBlocks()
    {
        if (false === $this->blocksGenerated) {
            $blocks = $this->updater->getLayoutStructure()
                ->get(self::INSTRUCTION_BLOCKS, []);
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

    /**
     * check if block has been removed
     *
     * @param string $blockId
     * @return boolean
     */
    protected function isBlockRemoved($blockId)
    {
        $removedBlocks = $this->updater->getLayoutStructure()
            ->get(self::INSTRUCTION_REMOVE_BLOCKS, false);
        if ($removedBlocks instanceof Config) {
            return $removedBlocks->get($blockId, false);
        }
        return false;
    }

    /**
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
     *
     * @param ModelInterface $root
     * @return LayoutManagerInterface
     */
    public function injectBlocks(ModelInterface $root)
    {
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
        if ($blockId === self::BLOCK_NAME_LAYOUT) {
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
     *
     * @param string $blockId
     * @param ModelInterface $block
     * @return LayoutManagerInterface
     */
    public function addBlock($blockId, ModelInterface $block)
    {
        $this->blocks[$blockId] = $block;
        return $this;
    }

    /**
     *
     * @param string $blockId
     * @return LayoutManagerInterface
     */
    public function removeBlock($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
        return $this;
    }

    /**
     * retrieve parent and capture_to as array, e.g.: [ 'layout', 'content' ]
     *
     * @param ModelInterface $block
     * @return array
     */
    protected function getCaptureTo(ModelInterface $block)
    {
        $captureTo = $block->captureTo();
        if (false !== strpos($captureTo, self::CAPTURE_TO_DELIMITER)) {
            return explode(self::CAPTURE_TO_DELIMITER, $captureTo);
        }
        return [
            self::BLOCK_NAME_LAYOUT,
            $captureTo
        ];
    }

    /**
     *
     * @param string $blockId
     * @return false|ModelInterface
     */
    public function getBlock($blockId)
    {
        $this->generateBlocks();
        return isset($this->blocks[$blockId])
            ? $this->blocks[$blockId]
            : false;
    }

    /**
     * @return ModelInterface[]
     */
    public function getBlocks()
    {
        $this->generateBlocks();
        return $this->blocks;
    }

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
