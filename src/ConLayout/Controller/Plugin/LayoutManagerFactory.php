<?php
namespace ConLayout\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManagerFactory implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Controller\Plugin\LayoutManager
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();
        return new LayoutManager(
            $sl->get('ConLayout\Service\BlocksBuilder'),
            $sl->get('ConLayout\Service\LayoutService'),
            $sl->get('ConLayout\View\Renderer\BlockRenderer')
        );
    }
}
