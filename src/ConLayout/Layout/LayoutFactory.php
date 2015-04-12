<?php

namespace ConLayout\Layout;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Layout
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $layout = new Layout(
            $serviceLocator->get('ConLayout\Block\Factory\BlockFactoryInterface'),
            $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface')
        );
        return $layout;
    }
}