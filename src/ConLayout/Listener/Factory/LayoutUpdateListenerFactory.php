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
        $listener = new LayoutUpdateListener($paths, $extensions);
        return $listener;
    }
}