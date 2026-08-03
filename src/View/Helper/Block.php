<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Layout\LayoutInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

class Block extends AbstractHelper
{
    private \ConLayout\Block\BlockPoolInterface $blockPool;

    public function __construct(BlockPoolInterface $blockPool)
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
