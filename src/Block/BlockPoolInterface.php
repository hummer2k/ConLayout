<?php

declare(strict_types=1);

namespace ConLayout\Block;

use Laminas\View\Model\ModelInterface;

interface BlockPoolInterface
{
    /**
     * @return ModelInterface[]|ModelInterface|false
     */
    public function get(?string $blockId = null): array|ModelInterface|false;

    public function add(string $blockId, ModelInterface $block): static;

    public function remove(string $blockId): void;

    public function sort(): static;
}
