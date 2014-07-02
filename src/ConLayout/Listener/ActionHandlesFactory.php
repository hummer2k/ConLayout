<?php
namespace ConLayout\Listener;

use Zend\ServiceManager\FactoryInterface,
    ConLayout\OptionTrait;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesFactory implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Listener\ActionHandles
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $layoutConfig = $serviceLocator->get('ConLayout\Service\Config');
        $behavior = $this->getOption($config, 'con-layout/handle_behavior', 'combined');
        return new ActionHandles($behavior, $layoutConfig);
    }
}
