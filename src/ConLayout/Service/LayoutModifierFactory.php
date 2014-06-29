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
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {        
        $renderer        = $serviceLocator->get('Zend\View\Renderer\PhpRenderer');        
        $pluginManager   = $renderer->getHelperPluginManager();
        $layout          = $pluginManager->get('viewModel')->getRoot();        
        $blockCollection = $serviceLocator->get('ConLayout\Service\BlocksBuilder')
            ->getCreatedBlocks();  
        $layoutTemplate  = $serviceLocator->get('ConLayout\Service\Config')
            ->getLayoutTemplate();
        return new LayoutModifier($layout, $blockCollection, $layoutTemplate); 
    }
}
