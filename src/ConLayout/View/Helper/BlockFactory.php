<?php

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockPoolInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Block
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();
        return new Block(
            $serviceManager->get(BlockPoolInterface::class)
        );
    }
}
