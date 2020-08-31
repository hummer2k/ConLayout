<?php

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Layout\LayoutInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Block extends AbstractHelper
{
    /**
     *
     * @var BlockPoolInterface
     */
    private $blockPool;

    /**
     *
     * @param BlockPoolInterface $blockPool
     */
    public function __construct(BlockPoolInterface $blockPool)
    {
        $this->blockPool = $blockPool;
    }

    /**
     *
     * @param string $blockId
     * @return ModelInterface|ViewModel
     */
    public function __invoke($blockId)
    {
        return $this->blockPool->get($blockId);
    }
}
