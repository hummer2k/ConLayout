<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ViewHelperListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperListenerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ViewHelperListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get(ModuleOptions::class);
        $viewHelperConfig = $options->getViewHelpers();
        $viewHelperListener = new ViewHelperListener(
            $serviceLocator->get(LayoutUpdaterInterface::class),
            $serviceLocator->get('ViewHelperManager'),
            $serviceLocator->get('FilterManager'),
            $viewHelperConfig
        );
        return $viewHelperListener;
    }
}
