<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Renderer\PhpRenderer;

class DebugFilter implements FilterInterface
{
    private PhpRenderer $phpRenderer;

    private ViewModelHelper $viewModelHelper;

    /**
     * DebugFilter constructor.
     */
    public function __construct(PhpRenderer $phpRenderer, ViewModelHelper $viewModelHelper)
    {
        $this->phpRenderer = $phpRenderer;
        $this->viewModelHelper = $viewModelHelper;
    }

    /**
     * @inheritDoc
     */
    public function filter($value): mixed
    {
        $viewModel = $this->viewModelHelper->getCurrent();
        $blockId = $viewModel?->getVariable('block_id');
        if (!$blockId) {
            return $value;
        }

        $opening = sprintf('<!--[%s]-->', $blockId);
        $closing = sprintf('<!--[/%s]-->', $blockId);
        if (0 !== strpos($value, $opening)) {
            $value = $opening . $value . $closing;
        }
        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
