<?php

namespace ConLayout\Block;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractBlock extends ViewModel implements BlockInterface
{
    /**
     *
     * @var RendererInterface
     */
    protected $view;

    /**
     *
     * @param RendererInterface $view
     * @return AbstractBlock
     */
    public function setView(RendererInterface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     *
     * @return RendererInterface
     */
    public function getView()
    {
        return $this->view;
    }
}
