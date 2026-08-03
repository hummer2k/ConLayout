<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConfigCollectorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ConfigCollector
    {
        $config = $container->get('config');
        $configStructure = isset($config['layout_updates'])
            ? (array) $config['layout_updates']
            : [];
        return new ConfigCollector($configStructure);
    }
}
