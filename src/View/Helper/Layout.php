<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\LayoutManager;

readonly class Layout
{
    public function __construct(private LayoutManager $layoutManager) {}

    public function __invoke(): LayoutManager
    {
        return $this->layoutManager;
    }
}
