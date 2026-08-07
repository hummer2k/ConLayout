<?php

declare(strict_types=1);

namespace ConLayout\View;

use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\DebugFilter;
use ConLayout\Options\ModuleOptions;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class PhpRendererDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $name,
        callable $callback,
        ?array $options = null,
    ): PhpRenderer {
        $renderer = $callback();
        if (!$renderer instanceof PhpRenderer) {
            return $renderer;
        }

        $viewModelHelper = $container->get(HelperPluginManager::class)->get(ViewModelHelper::class);
        $containerFilter = new ContainerFilter($renderer, $viewModelHelper);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $renderer->setFilter(
            $moduleOptions->isDebug()
                ? fn(string $value): string => (new DebugFilter($viewModelHelper))
                    ->filter($containerFilter->filter($value))
                : fn(string $value): string => $containerFilter->filter($value),
        );

        return $renderer;
    }
}
