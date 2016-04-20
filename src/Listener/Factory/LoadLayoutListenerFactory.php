<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LoadLayoutListener;
use Interop\Container\ContainerInterface;
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
        return $this($serviceLocator, LoadLayoutListener::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return LoadLayoutListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $injectBlocksListener = new LoadLayoutListener(
            $container->get(LayoutInterface::class)
        );
        return $injectBlocksListener;
    }
}
