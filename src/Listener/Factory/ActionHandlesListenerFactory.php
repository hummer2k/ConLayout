<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdaterInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return ActionHandlesListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var $moduleOptions ModuleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $updater       = $container->get(LayoutUpdaterInterface::class);
        $controllerMap = $moduleOptions->getControllerMap();

        $actionHandlesListener = new ActionHandlesListener($updater);
        $actionHandlesListener->setControllerMap($controllerMap);

        return $actionHandlesListener;
    }
}
