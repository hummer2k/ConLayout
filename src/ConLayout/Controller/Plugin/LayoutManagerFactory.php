<?php
namespace ConLayout\Controller\Plugin;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Renderer\BlockRenderer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManagerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return LayoutManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();
        return new LayoutManager(
            $sl->get(LayoutInterface::class),
            $sl->get(LayoutUpdaterInterface::class),
            $sl->get(BlockRenderer::class)
        );
    }
}
