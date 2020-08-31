<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Ldt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayout\Updater\LayoutUpdaterInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutCollectorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return LayoutCollector
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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
