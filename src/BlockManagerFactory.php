<?php

declare(strict_types=1);

namespace ConLayout;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BlockManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): BlockManager
    {
        $config = $container->has('config') ? $container->get('config') : [];
        return new BlockManager($container, $config['blocks'] ?? []);
    }
}
