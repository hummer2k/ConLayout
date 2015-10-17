<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LoadLayoutListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LoadLayoutListenerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return LoadLayoutListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $injectBlocksListener = new LoadLayoutListener(
            $serviceLocator->get(LayoutInterface::class)
        );
        return $injectBlocksListener;
    }
}
