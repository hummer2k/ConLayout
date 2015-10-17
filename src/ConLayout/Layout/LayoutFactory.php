<?php

namespace ConLayout\Layout;

use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
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
            $serviceLocator->get(BlockFactoryInterface::class),
            $serviceLocator->get(LayoutUpdaterInterface::class)
        );
        return $layout;
    }
}
