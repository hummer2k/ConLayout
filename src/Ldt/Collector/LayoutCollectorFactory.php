<?php

declare(strict_types=1);

namespace ConLayout\Ldt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayout\Updater\LayoutUpdaterInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutCollectorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LayoutCollector
    {
        $layoutCollector = new LayoutCollector(
            $container->get(LayoutInterface::class),
            $container->get(LayoutUpdaterInterface::class),
            $container->get('ViewResolver'),
            $container->get(ModuleOptions::class),
            $container->get(FilesystemCollector::class)
        );
        return $layoutCollector;
    }
}
