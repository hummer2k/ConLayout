<?php
namespace ConLayout\View\Strategy;

use ConLayout\View\Renderer\BlockRenderer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererStrategyFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return BlockRendererStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $blockRenderer =  $serviceLocator->get(BlockRenderer::class);
        $blockRendererStrategy = new BlockRendererStrategy(
            $blockRenderer
        );
        return $blockRendererStrategy;
    }
}
