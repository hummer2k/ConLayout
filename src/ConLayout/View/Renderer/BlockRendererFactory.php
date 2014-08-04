<?php
namespace ConLayout\View\Renderer;

use Zend\ServiceManager\FactoryInterface,
    ConLayout\OptionTrait;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererFactory implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\View\Renderer\BlockRenderer
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $viewManager = $serviceLocator->get('ViewManager');
        $config = $serviceLocator->get('Config');
        $blockRenderer = new BlockRenderer();
        $blockRenderer->setHelperPluginManager($viewManager->getHelperManager());
        $blockRenderer->setResolver($viewManager->getResolver());
        $cacheEnabled = $this->getOption($config, 'con-layout/enable_block_cache', true);
        
        if ($cacheEnabled) {
            $cache = $this->getOption($config, 'con-layout/block_cache', 'ConLayout\Cache');
            $blockRenderer
                ->setCache($serviceLocator->get($cache))
                ->setCacheEnabled();
        }        
        return $blockRenderer;
    }
}
