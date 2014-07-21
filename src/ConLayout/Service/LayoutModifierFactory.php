<?php

namespace ConLayout\Service;

use Zend\ServiceManager\FactoryInterface;

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
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {        
        $config = $serviceLocator->get('Config');
        $layout = $serviceLocator->get('Zend\View\Renderer\PhpRenderer')
            ->viewModel()
            ->getRoot();
        
        $createdBlocks   = $serviceLocator->get('ConLayout\Service\BlocksBuilder')
            ->getCreatedBlocks();
        
        $layoutModifier = new LayoutModifier($layout, $createdBlocks); 
        $layoutModifier
            ->setIsDebug($this->getOption($config, 'con-layout/enable_debug', false))
            ->setCaptureTo($this->getOption($config, 'con-layout/child_capture_to', 'childHtml'));            
        
        return $layoutModifier;
    }
}
