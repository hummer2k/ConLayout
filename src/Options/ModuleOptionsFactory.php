<?php

declare(strict_types=1);

namespace ConLayout\Options;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): ModuleOptions
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $options = $config['con-layout'] ?? [];
        return new ModuleOptions($options);
    }
}
