<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class ContainerFilter implements FilterInterface
{
    private \Laminas\View\Renderer\PhpRenderer $phpRenderer;

    private ?\Laminas\View\Model\ModelInterface $currentViewModel = null;

    /**
     * ContainerFilter constructor.
     */
    public function __construct(PhpRenderer $phpRenderer)
    {
        $this->phpRenderer = $phpRenderer;
    }

    /**
     * @param mixed $value
     */
    public function filter($value): mixed
    {
        /** @var ViewModel $viewModel */
        $viewModel = $this->phpRenderer->viewModel()->getCurrent();
        $vars = $this->phpRenderer->vars();
        if (
            !$viewModel instanceof ModelInterface ||
            $vars->count() ||
            $viewModel === $this->currentViewModel
        ) {
            return $value;
        }

        $container = trim((string) $viewModel->getOption('container'));
        if (!strlen($container)) {
            return $value;
        }
        $container = trim($container);
        if (false !== strpos($container, '<')) {
            $value = sprintf($container, $value);
        } else {
            $this->currentViewModel = $viewModel;
            $values = (array) $viewModel->getVariables();
            $templates = explode(',', $container);
            foreach ($templates as $template) {
                $values['content'] = $value;
                $value = $this->phpRenderer->render(trim($template), $values);
            }
            $this->currentViewModel = null;
        }

        return $value;
    }
}
