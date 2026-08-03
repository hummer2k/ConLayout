<?php

declare(strict_types=1);

namespace ConLayout\Block;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;

abstract class AbstractBlock extends ViewModel implements BlockInterface
{
    protected ?RendererInterface $view = null;

    public function setView(RendererInterface $view): static
    {
        $this->view = $view;
        return $this;
    }

    public function getView(): ?RendererInterface
    {
        return $this->view;
    }
}
