<?php
namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Options\ModuleOptions;
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
        $moduleOptions = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $actionHandlesListener = new ActionHandlesListener(
            $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface'),
            $moduleOptions->getExcludeActionHandleSegments()
        );
        return $actionHandlesListener;
    }
}
