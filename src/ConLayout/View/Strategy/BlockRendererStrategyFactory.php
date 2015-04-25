<?php
namespace ConLayout\View\Strategy;

use Zend\ServiceManager\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererStrategyFactory implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\View\Strategy\BlockRendererStrategy
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $blockRenderer =  $serviceLocator->get('ConLayout\View\Renderer\BlockRenderer');
        $blockRendererStrategy = new BlockRendererStrategy(
            $blockRenderer
        );
        return $blockRendererStrategy;
    }
}
