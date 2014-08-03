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
        $layoutService = $serviceLocator->get('ConLayout\Service\LayoutService');
        $behavior = $this->getOption($config, 'con-layout/handle_behavior', 'combined');
        $helperConfig = $this->getOption($config, 'con-layout/helpers', array());
        $actionHandles = new ActionHandles($behavior, $layoutService, $helperConfig);
        foreach ($helperConfig as $helper => $value) {
            if (is_array($value) && isset($value['valuePreparers'])) {
                foreach ($value['valuePreparers'] as $valuePreparer) {
                    $actionHandles->addValuePreparer($helper, $serviceLocator->get($valuePreparer));
                }
                unset($helperConfig[$helper]['valuePreparers']);
            }
        }
        return $actionHandles;
    }
}
