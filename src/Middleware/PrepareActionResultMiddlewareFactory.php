<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Block\BlockPoolInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PrepareActionResultMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): PrepareActionResultMiddleware {
        return new PrepareActionResultMiddleware(
            $container->get(BlockPoolInterface::class)
        );
    }
}
