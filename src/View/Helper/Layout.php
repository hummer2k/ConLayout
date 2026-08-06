<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\LayoutManager;

class Layout
{
    public function __construct(private readonly LayoutManager $layoutManager) {}

    public function __invoke(): LayoutManager
    {
        return $this->layoutManager;
    }
}
