<?php
namespace ConLayout\View\Renderer;

use Zend\ServiceManager\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererFactory implements FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $viewManager = $serviceLocator->get('ViewManager');
        $blockRenderer = new BlockRenderer();
        $blockRenderer->setHelperPluginManager($viewManager->getHelperManager());
        $blockRenderer->setResolver($viewManager->getResolver());
        return $blockRenderer;
    }
}
