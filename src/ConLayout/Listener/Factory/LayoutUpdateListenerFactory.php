<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $paths = $options->getLayoutUpdatePaths();
        $extensions = $options->getLayoutUpdateExtensions();
        $defaultArea = $options->getDefaultArea();
        $listener = new LayoutUpdateListener($paths, $extensions, $defaultArea);
        return $listener;
    }
}
