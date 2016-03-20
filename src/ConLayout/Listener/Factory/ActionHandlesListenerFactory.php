<?php
namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdaterInterface;
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
        /* @var $moduleOptions ModuleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);
        $updater = $serviceLocator->get(LayoutUpdaterInterface::class);
        $actionHandlesListener = new ActionHandlesListener();
        $actionHandlesListener->setUpdater($updater);
        $actionHandlesListener->setControllerMap($moduleOptions->getControllerMap());
        $actionHandlesListener->setPreferRouteMatchController($moduleOptions->isPreferRouteMatchController());

        return $actionHandlesListener;
    }
}
