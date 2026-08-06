<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockRegistryInterface;
use Laminas\View\Model\ModelInterface;

class Block
{
    public function __construct(private readonly BlockRegistryInterface $blockPool) {}

    /**
     *
     * @return ModelInterface|false
     */
    public function __invoke(?string $blockId): ModelInterface|false
    {
        return $this->blockPool->get($blockId);
    }
}
