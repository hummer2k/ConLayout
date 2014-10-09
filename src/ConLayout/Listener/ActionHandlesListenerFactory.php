<?php
namespace ConLayout\Listener;

use ConLayout\OptionTrait,
    Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;
/**
 * @package 
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
        $config = $serviceLocator->get('Config');
        $layoutService = $serviceLocator->get('ConLayout\Service\LayoutService');
        $behavior = $this->getOption($config, 'con-layout/handle_behavior', 'combined');
        $actionHandles = new ActionHandlesListener($behavior, $layoutService);
        return $actionHandles;
    }
}
