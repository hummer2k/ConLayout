<?php

declare(strict_types=1);

namespace ConLayout\Block;

use Laminas\View\Model\ModelInterface;

interface BlockRegistryInterface
{
    public function get(string $blockId): ModelInterface|false;

    /**
     * retrieve all blocks
     *
     * @return array<array-key, ModelInterface>
     */
    public function getAll(): array;

    public function add(string $blockId, ModelInterface $block): static;

    public function remove(string $blockId): void;

    /**
     * @return array<int, string> ids of all blocks that were removed
     */
    public function getRemovedBlocks(): array;

    public function sort(): static;
}
