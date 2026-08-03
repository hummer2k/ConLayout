<?php

declare(strict_types=1);

namespace ConLayout\Options;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ModuleOptions
    {
        $config = $container->get('Config');
        $options = isset($config['con-layout']) ? $config['con-layout'] : [];

        if (
            !isset($options['controller_map'])
            && isset($config['view_manager']['controller_map'])
        ) {
            $options['controller_map'] = (array) $config['view_manager']['controller_map'];
        }

        $moduleOptions = new ModuleOptions(
            $options
        );
        return $moduleOptions;
    }
}
