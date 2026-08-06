<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;

readonly class ContainerFilter implements FilterInterface
{
    public function __construct(
        private PhpRenderer $phpRenderer,
        private ViewModelHelper $viewModelHelper,
    ) {}

    public function filter($value): mixed
    {
        $viewModel = $this->viewModelHelper->getCurrent();
        if (!$viewModel instanceof ModelInterface) {
            return $value;
        }

        if ($viewModel->getVariable('block_id') === null) {
            return $value;
        }

        $container = trim((string) ($viewModel->getVariable('container') ?? ''));
        if (!strlen($container)) {
            return $value;
        }
        $container = trim($container);
        if (str_contains($container, '<')) {
            $value = sprintf($container, $value);
        } else {
            $values = (array) $viewModel->getVariables();
            unset(
                $values['block_id'],
                $values['container'],
                $values['wrapper'],
                $values['parent'],
                $values['order'],
                $values['before'],
                $values['after'],
                $values['has_parent'],
                $values['parent_block'],
                $values['tag'],
            );
            $templates = explode(',', $container);
            foreach ($templates as $template) {
                $values['content'] = $value;
                $value = $this->phpRenderer->render(trim($template), $values);
            }
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
