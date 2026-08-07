<?php

declare(strict_types=1);

namespace ConLayout\Block;

use ConLayout\Layout\LayoutInterface;
use Laminas\View\Model\ClearableModelInterface;
use Laminas\View\Model\ModelInterface;

final class BlockRegistry implements BlockRegistryInterface
{
    public const ANONYMOUS_ID_PATTERN = 'anonymous.%s.%s';

    /**
     * suffix for anonymous block names
     * will be incremented on every addBlock call when block has no id
     */
    private static int $anonymousSuffix = 1;

    /**
     * @var ModelInterface[]
     */
    private array $blocks = [];

    private array $removedBlocks = [];

    /**
     * @inheritDoc
     */
    public function get(string $blockId): ModelInterface|false
    {
        return isset($this->blocks[$blockId])
            ? $this->blocks[$blockId]
            : false;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->blocks;
    }

    /**
     * @inheritDoc
     */
    public function add(string $blockId, ModelInterface $block): static
    {
        $block->setVariable('blockId', $blockId);
        if ($block->hasChildren()) {
            foreach ($block->getChildren() as $childBlock) {
                $childBlockId = $this->determineAnonymousBlockId($childBlock);
                $childBlock->setCaptureTo(
                    $blockId . LayoutInterface::CAPTURE_TO_DELIMITER . $childBlock->captureTo(),
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
    public function remove(string $blockId): void
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
        $this->removedBlocks[$blockId] = true;
    }

    /**
     * @return array<int, string>
     */
    public function getRemovedBlocks(): array
    {
        return array_keys($this->removedBlocks);
    }

    /**
     * @inheritDoc
     */
    public function sort(): static
    {
        $sortedBlocks = [];
        foreach ($this->groupByParent() as $group) {
            $this->resolveBeforeAfter($group);
            uasort($group, fn($a, $b) => $a->getVariable('order', 0) <=> $b->getVariable('order', 0));
            foreach ($group as $blockId => $block) {
                $sortedBlocks[$blockId] = $block;
            }
        }
        $this->blocks = $sortedBlocks;
        return $this;
    }

    /**
     * @return array<string, array<string, ModelInterface>>
     */
    private function groupByParent(): array
    {
        $groups = [];
        foreach ($this->blocks as $blockId => $block) {
            $groups[$this->getParentId($block)][$blockId] = $block;
        }
        return $groups;
    }

    private function getParentId(ModelInterface $block): string
    {
        if ($parentId = $block->getVariable('parentId')) {
            return $parentId;
        }
        if (str_contains($block->captureTo(), LayoutInterface::CAPTURE_TO_DELIMITER)) {
            [$parent] = explode(LayoutInterface::CAPTURE_TO_DELIMITER, $block->captureTo());
            return $parent;
        }
        return LayoutInterface::BLOCK_ID_ROOT;
    }

    /**
     * @param array<string, ModelInterface> $blocks
     */
    private function resolveBeforeAfter(array $blocks): void
    {
        foreach ($blocks as $block) {
            if ($beforeBlockId = $block->getVariable('before')) {
                if (isset($blocks[$beforeBlockId])) {
                    $block->setVariable('order', $blocks[$beforeBlockId]->getVariable('order', 0) - 1);
                }
            }
            if ($afterBlockId = $block->getVariable('after')) {
                if (isset($blocks[$afterBlockId])) {
                    $block->setVariable('order', $blocks[$afterBlockId]->getVariable('order', 0) + 1);
                }
            }
        }
    }

    private function determineAnonymousBlockId(ModelInterface $block): string
    {
        $blockId = $block->getVariable('blockId');
        if (!$blockId) {
            $blockId = sprintf(
                self::ANONYMOUS_ID_PATTERN,
                $block->captureTo(),
                self::$anonymousSuffix++,
            );
            $block->setVariable('blockId', $blockId);
        }
        return $blockId;
    }
}
