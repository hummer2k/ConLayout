<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\PhpRenderer;

class DebugFilterFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DebugFilter|ContainerFilter {
        return new $requestedName($container->get(PhpRenderer::class));
    }
}
