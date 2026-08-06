<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockRegistryInterface;
use Laminas\View\Model\ModelInterface;

readonly class Block
{
    public function __construct(private BlockRegistryInterface $blockRegistry) {}

    /**
     *
     * @return ModelInterface|false
     */
    public function __invoke(?string $blockId): ModelInterface|false
    {
        return $this->blockRegistry->get($blockId);
    }
}
