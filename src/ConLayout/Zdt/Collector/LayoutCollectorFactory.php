<?php

namespace ConLayout\Zdt\Collector;

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
            $serviceLocator->get('ConLayout\Layout\LayoutInterface'),
            $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface')
        );
        return $layoutCollector;
    }
}