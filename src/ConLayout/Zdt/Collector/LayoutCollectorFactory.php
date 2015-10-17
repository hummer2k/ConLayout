<?php

namespace ConLayout\Zdt\Collector;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollectorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $layoutCollector = new LayoutCollector(
            $serviceLocator->get(LayoutInterface::class),
            $serviceLocator->get(LayoutUpdaterInterface::class),
            $serviceLocator->get('ViewResolver')
        );
        return $layoutCollector;
    }
}
