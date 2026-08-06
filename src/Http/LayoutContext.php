<?php

declare(strict_types=1);

namespace ConLayout\Http;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\LayoutManager;

class LayoutContext
{
    /**
     * @var HandleInterface[]
     */
    private array $handles = [];

    public function __construct(private readonly LayoutManager $layoutManager)
    {
        $this->addHandle(new Handle('default', -1));
    }

    public function getLayoutManager(): LayoutManager
    {
        return $this->layoutManager;
    }

    /**
     * @return HandleInterface[]
     */
    public function getHandles(): array
    {
        return $this->handles;
    }

    /**
     * @param HandleInterface[] $handles
     */
    public function setHandles(array $handles): static
    {
        $this->handles = $handles;
        return $this;
    }

    public function addHandle(HandleInterface $handle): static
    {
        $this->handles[] = $handle;
        return $this;
    }
}
