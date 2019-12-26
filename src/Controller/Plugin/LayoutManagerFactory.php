<?php

namespace ConLayout\Controller\Plugin;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return LayoutManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new LayoutManager(
            $container->get(LayoutInterface::class),
            $container->get(LayoutUpdaterInterface::class),
            $container->get(BlockPoolInterface::class)
        );
    }
}
