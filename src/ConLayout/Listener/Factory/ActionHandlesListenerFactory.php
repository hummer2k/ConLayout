<?php
namespace ConLayout\Listener\Factory;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\OptionTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerFactory implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return ActionHandlesListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $actionHandlesListener = new ActionHandlesListener(
            $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface')
        );
        return $actionHandlesListener;
    }
}
