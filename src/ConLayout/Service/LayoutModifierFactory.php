<?php

namespace ConLayout\Service;

use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ModifierFactory
 *
 * @author hummer 
 */
class LayoutModifierFactory
    implements FactoryInterface
{
    use \ConLayout\OptionTrait;
    
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $config = $serviceLocator->get('Config');        
        $layoutModifier = new LayoutModifier(); 
        $layoutModifier
            ->setIsDebug($this->getOption($config, 'con-layout/enable_debug', false))
            ->setCaptureTo($this->getOption($config, 'con-layout/child_capture_to', 'childHtml'));            
        
        return $layoutModifier;
    }
}
