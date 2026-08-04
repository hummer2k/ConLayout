<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\LayoutManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutContextMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): LayoutContextMiddleware {
        return new LayoutContextMiddleware(
            $container->get(LayoutManager::class),
        );
    }
}
