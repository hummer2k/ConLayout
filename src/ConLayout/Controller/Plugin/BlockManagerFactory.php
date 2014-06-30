<?php
namespace ConLayout\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManagerFactory implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Controller\Plugin\BlockManager
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();
        return new BlockManager(
            $sl->get('ConLayout\Service\BlocksBuilder'),
            $sl->get('ConLayout\Service\Config'),
            $sl->get('Zend\View\Renderer\PhpRenderer')
        );
    }
}
