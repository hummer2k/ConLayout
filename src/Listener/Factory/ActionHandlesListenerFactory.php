<?php

declare(strict_types=1);

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdaterInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ActionHandlesListenerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ActionHandlesListener {
        /** @var $moduleOptions ModuleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $updater       = $container->get(LayoutUpdaterInterface::class);
        $controllerMap = $moduleOptions->getControllerMap();

        $actionHandlesListener = new ActionHandlesListener($updater);
        $actionHandlesListener->setControllerMap($controllerMap);

        return $actionHandlesListener;
    }
}
