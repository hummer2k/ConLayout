<?php

declare(strict_types=1);

namespace ConLayout\Toolbar;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class ToolbarMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ToolbarMiddleware {
        $config = $container->has('config') ? $container->get('config') : [];

        return new ToolbarMiddleware(
            new ToolbarData(
                $container->get(LayoutManager::class),
                $container->has(FilesystemCollector::class) ? $container->get(FilesystemCollector::class) : null,
                $container->has(BlockPoolInterface::class) ? $container->get(BlockPoolInterface::class) : null,
                $container->has(ModuleOptions::class) ? $container->get(ModuleOptions::class) : null
            ),
            $container->get(PhpRenderer::class),
            (bool) ($config['debug'] ?? false)
        );
    }
}
