<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListenerFactory implements FactoryInterface
{
    use \ConLayout\OptionTrait;

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return LayoutUpdateListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $layoutUpdateListener = new LayoutUpdateListener(
            $options->getUpdateListenerGlobPaths()
        );
        return $layoutUpdateListener;
    }
}
