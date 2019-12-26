<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Block;

use ConLayout\Layout\LayoutInterface;
use Zend\View\Model\ClearableModelInterface;
use Zend\View\Model\ModelInterface;

final class BlockPool implements BlockPoolInterface
{
    public const ANONYMOUS_ID_PATTERN = 'anonymous.%s.%s';

    /**
     * suffix for anonymous block names
     * will be incremented on every addBlock call when block has no id
     *
     * @var int
     */
    private static $anonymousSuffix = 1;

    /**
     * @var ModelInterface[]|BlockInterface[]
     */
    private $blocks = [];

    /**
     * @var array
     */
    private $removedBlocks = [];

    /**
     * @inheritDoc
     */
    public function get($blockId = null)
    {
        if (null === $blockId) {
            return $this->blocks;
        }
        return isset($this->blocks[$blockId])
            ? $this->blocks[$blockId]
            : false;
    }

    /**
     * @inheritDoc
     */
    public function add($blockId, ModelInterface $block)
    {
        $block->setOption('block_id', $blockId);
        if ($block->hasChildren()) {
            foreach ($block->getChildren() as $childBlock) {
                $childBlockId = $this->determineAnonymousBlockId($childBlock);
                $childBlock->setCaptureTo(
                    $blockId . LayoutInterface::CAPTURE_TO_DELIMITER . $childBlock->captureTo()
                );
                $this->add($childBlockId, $childBlock);
            }
            if ($block instanceof ClearableModelInterface) {
                $block->clearChildren();
            }
        }
        $this->blocks[$blockId] = $block;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remove($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
        $this->removedBlocks[$blockId] = true;
    }

    /**
     * @inheritDoc
     */
    public function sort()
    {
        foreach ($this->blocks as $block) {
            if ($beforeBlockId = $block->getOption('before')) {
                if ($beforeBlock = $this->get($beforeBlockId)) {
                    $block->setOption('order', $beforeBlock->getOption('order', 0) - 1);
                }
            }
            if ($afterBlockId = $block->getOption('after')) {
                if ($afterBlock = $this->get($afterBlockId)) {
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
     * @param ModelInterface $block
     * @return mixed|string
     */
    private function determineAnonymousBlockId(ModelInterface $block)
    {
        $blockId = $block->getOption('block_id');
        if (!$blockId) {
            $blockId = sprintf(
                self::ANONYMOUS_ID_PATTERN,
                $block->captureTo(),
                self::$anonymousSuffix++
            );
            $block->setOption('block_id', $blockId);
        }
        return $blockId;
    }
}
