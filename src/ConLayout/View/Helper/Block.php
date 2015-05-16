<?php

namespace ConLayout\View\Helper;

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
    protected $layout;

    /**
     *
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     *
     * @param string $blockId
     * @return ModelInterface
     */
    public function __invoke($blockId)
    {
        return $this->layout->getBlock($blockId);
    }
}
