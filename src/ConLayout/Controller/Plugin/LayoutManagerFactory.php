<?php
namespace ConLayout\Controller\Plugin;

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
            $sl->get('ConLayout\Layout\LayoutInterface'),
            $sl->get('ConLayout\Updater\LayoutUpdaterInterface'),
            $sl->get('ConLayout\View\Renderer\BlockRenderer')
        );
    }
}
