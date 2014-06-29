<?php
namespace ConLayout\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlocksFactory implements FactoryInterface
{
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();
        return new Blocks(
            $sl->get('ConLayout\Service\BlocksBuilder'),
            $sl->get('ConLayout\Service\Config'),
            $sl->get('Zend\View\Renderer\PhpRenderer')
        );
    }
}
