<?php
namespace ConLayout\View\Renderer;

use ConLayout\OptionTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererFactory implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return BlockRenderer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $blockRenderer = new BlockRenderer();
        $blockRenderer->setHelperPluginManager(
            $serviceLocator->get('ViewHelperManager')
        );
        $blockRenderer->setResolver(
            $serviceLocator->get('Zend\View\Resolver\AggregateResolver')
        );
        return $blockRenderer;
    }
}
