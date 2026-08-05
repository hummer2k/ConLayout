<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\LayoutManager;

class Layout
{
    private LayoutManager $layoutManager;

    public function __construct(LayoutManager $layoutManager)
    {
        $this->layoutManager = $layoutManager;
    }

    public function __invoke(): LayoutManager
    {
        return $this->layoutManager;
    }
}
