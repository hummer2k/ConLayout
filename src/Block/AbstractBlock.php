<?php

namespace ConLayout\Block;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

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
