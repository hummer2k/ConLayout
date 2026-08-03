<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\ViewModel;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Variables;

class DebugFilter implements FilterInterface
{
    private PhpRenderer $phpRenderer;

    /**
     * DebugFilter constructor.
     */
    public function __construct(PhpRenderer $phpRenderer)
    {
        $this->phpRenderer = $phpRenderer;
    }

    /**
     * @inheritDoc
     */
    public function filter($value): mixed
    {
        /** @var ViewModel $viewModel */
        $viewModel = $this->phpRenderer->viewModel()->getCurrent();

        if (!$viewModel instanceof ModelInterface) {
            return $value;
        }

        /** @var Variables $vars */
        $vars = $this->phpRenderer->vars();
        $blockId = $viewModel->getOption('block_id');
        if ($blockId && !$vars->count()) {
            $opening = sprintf('<!--[%s]-->', $blockId);
            $closing = sprintf('<!--[/%s]-->', $blockId);
            if (0 !== strpos($value, $opening)) {
                $value = $opening . $value . $closing;
            }
        }
        return $value;
    }
}
