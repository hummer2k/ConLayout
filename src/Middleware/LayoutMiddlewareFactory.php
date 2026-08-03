<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\LayoutManager;
use ConLayout\Options\ModuleOptions;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;
use Mezzio\LaminasView\NamespacedPathStackResolver;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): LayoutMiddleware {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        $config = $container->has('config') ? $container->get('config') : [];
        $layoutTemplate = $moduleOptions->getLayoutTemplate();
        if (!$layoutTemplate) {
            $layoutTemplate = (string) ($config['templates']['layout'] ?? '');
        }

        $phpRenderer = $container->get(PhpRenderer::class);
        $phpRenderer->setResolver($this->createResolver($config['templates'] ?? []));

        return new LayoutMiddleware(
            $container->get(LayoutManager::class),
            $phpRenderer,
            $moduleOptions,
            $layoutTemplate
        );
    }

    private function createResolver(array $config): AggregateResolver
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver($config['map'] ?? []), 100);

        $namespacedResolver = new NamespacedPathStackResolver();
        foreach ($config['paths'] ?? [] as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $namespacedResolver->addPath($path, $namespace);
            }
        }
        $resolver->attach($namespacedResolver, 0);

        return $resolver;
    }
}
