<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class ContainerFilter implements FilterInterface
{
    /**
     * @var PhpRenderer
     */
    private PhpRenderer $phpRenderer;

    /**
     * @var ModelInterface
     */
    private $currentViewModel;

    /**
     * ContainerFilter constructor.
     * @param PhpRenderer $phpRenderer
     */
    public function __construct(PhpRenderer $phpRenderer)
    {
        $this->phpRenderer = $phpRenderer;
    }

    /**
     * @param string $value
     * @return string
     */
    public function filter($value)
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

        $container = trim($viewModel->getOption('container'));
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
