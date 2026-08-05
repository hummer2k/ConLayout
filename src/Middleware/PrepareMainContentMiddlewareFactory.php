<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Block\BlockRegistryInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PrepareMainContentMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): PrepareMainContentMiddleware {
        return new PrepareMainContentMiddleware(
            $container->get(BlockRegistryInterface::class),
        );
    }
}
