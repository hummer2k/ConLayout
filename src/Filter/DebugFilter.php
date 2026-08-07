<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\ViewModel as ViewModelHelper;

/**
 * @template-implements FilterInterface<mixed>
 */
readonly class DebugFilter implements FilterInterface
{
    public function __construct(private ViewModelHelper $viewModelHelper) {}

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        $viewModel = $this->viewModelHelper->getCurrent();
        $blockId = $viewModel?->getVariable('blockId');
        if (null === $blockId || '' === $blockId) {
            return $value;
        }

        $opening = sprintf('<!--[%s]-->', $blockId);
        $closing = sprintf('<!--[/%s]-->', $blockId);
        if (!str_starts_with($value, $opening)) {
            $value = $opening . $value . $closing;
        }
        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
