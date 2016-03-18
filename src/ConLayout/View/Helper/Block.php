<?php

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockPool;
use ConLayout\Block\BlockPoolInterface;
use ConLayout\Layout\LayoutInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Block extends AbstractHelper
{
    /**
     *
     * @var LayoutInterface
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
     * @return ModelInterface
     */
    public function __invoke($blockId)
    {
        return $this->blockPool->get($blockId);
    }
}
