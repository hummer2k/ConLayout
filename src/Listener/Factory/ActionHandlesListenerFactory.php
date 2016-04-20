<?php
namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdaterInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ActionHandlesListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ActionHandlesListener::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return ActionHandlesListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $moduleOptions ModuleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        $updater = $container->get(LayoutUpdaterInterface::class);
        $actionHandlesListener = new ActionHandlesListener();
        $actionHandlesListener->setUpdater($updater);
        $actionHandlesListener->setControllerMap($moduleOptions->getControllerMap());
        $actionHandlesListener->setPreferRouteMatchController($moduleOptions->isPreferRouteMatchController());

        return $actionHandlesListener;
    }
}
