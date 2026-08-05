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
        $requestedName,
        ?array $options = null,
    ): DebugFilter|ContainerFilter {
        return new $requestedName(
            $container->get(PhpRenderer::class),
            $container->get(HelperPluginManager::class)->get(ViewModelHelper::class),
        );
    }
}
