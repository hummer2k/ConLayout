<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockRegistryInterface;
use Laminas\View\Model\ModelInterface;

class Block
{
    private BlockRegistryInterface $blockPool;

    public function __construct(BlockRegistryInterface $blockPool)
    {
        $this->blockPool = $blockPool;
    }

    /**
     *
     * @return ModelInterface|false
     */
    public function __invoke(?string $blockId): ModelInterface|false
    {
        return $this->blockPool->get($blockId);
    }
}
