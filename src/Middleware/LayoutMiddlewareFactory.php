<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class LayoutMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): LayoutMiddleware {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        $config = $container->has('config') ? $container->get('config') : [];
        $layoutTemplate = $moduleOptions->getLayoutTemplate();
        if (!$layoutTemplate) {
            $layoutTemplate = (string) ($config['templates']['layout'] ?? '');
        }

        return new LayoutMiddleware(
            $container->get(LayoutManager::class),
            $container->get(PhpRenderer::class),
            $moduleOptions,
            $container->get(HelperPluginManager::class),
            $layoutTemplate,
        );
    }
}
