<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\ViewModel as ViewModelHelper;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class DebugFilterFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): DebugFilter|ContainerFilter {
        $phpRenderer = $container->get(PhpRenderer::class);
        $viewModelHelper = $container->get(HelperPluginManager::class)->get(ViewModelHelper::class);
        return DebugFilter::class === $requestedName
            ? new DebugFilter($viewModelHelper)
            : new ContainerFilter($phpRenderer, $viewModelHelper);
    }
}
