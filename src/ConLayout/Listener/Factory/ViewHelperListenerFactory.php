<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ViewHelperListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperListenerFactory implements FactoryInterface
{
    use \ConLayout\OptionTrait;

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ViewHelperListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $viewHelperConfig = $this->getOption($config, 'con-layout/view_helpers', []);
        $viewHelperListener = new ViewHelperListener(
            $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface'),
            $serviceLocator->get('ViewHelperManager'),
            $viewHelperConfig
        );
        return $viewHelperListener;
    }
}
