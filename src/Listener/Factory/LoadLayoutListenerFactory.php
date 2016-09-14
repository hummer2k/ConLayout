<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LoadLayoutListener;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LoadLayoutListenerFactory implements FactoryInterface
{
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
