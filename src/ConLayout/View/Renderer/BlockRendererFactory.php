<?php
namespace ConLayout\View\Renderer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Resolver\AggregateResolver;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererFactory implements FactoryInterface
{
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
            $serviceLocator->get(AggregateResolver::class)
        );
        return $blockRenderer;
    }
}
