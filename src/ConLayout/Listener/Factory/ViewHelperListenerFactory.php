<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ViewHelperListener;
use ConLayout\Options\ModuleOptions;
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
        $options = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $viewHelperConfig = $options->getViewHelpers();
        $assetPreparers = [];
        foreach ($options->getAssetPreparers() as $alias => $fqcn) {
            $assetPreparers[$alias] = $serviceLocator->get($fqcn);
        }
        $viewHelperListener = new ViewHelperListener(
            $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface'),
            $serviceLocator->get('ViewHelperManager'),
            $viewHelperConfig,
            $assetPreparers
        );
        return $viewHelperListener;
    }
}
