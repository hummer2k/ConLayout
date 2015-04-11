<?php
namespace ConLayout\Listener\Factory;

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
        $config = $serviceLocator->get('Config');
        $layoutService = $serviceLocator->get('ConLayout\Service\LayoutService');
        $behavior = $this->getOption($config, 'con-layout/handle_behavior', 'combined');
        $actionHandles = new ActionHandlesListener($behavior, $layoutService);
        return $actionHandles;
    }
}
