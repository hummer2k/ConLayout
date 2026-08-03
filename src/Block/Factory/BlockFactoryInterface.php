<?php

declare(strict_types=1);

namespace ConLayout\Block\Factory;

use Laminas\View\Model\ModelInterface;

interface BlockFactoryInterface
{
    public function createBlock(string $blockId, array $specs): ModelInterface;

    public function configure(ModelInterface $block, array $specs): ModelInterface;
}
