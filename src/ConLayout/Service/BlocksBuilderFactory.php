<?php
namespace ConLayout\Service;

use Zend\ServiceManager\FactoryInterface;

/**
 * BlocksBuilderFactory
 *
 * @author hummer 
 */
class BlocksBuilderFactory
    implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\BlocksBuilder
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $layoutService = $serviceLocator->get('ConLayout\Service\LayoutService');
        $blocksBuilder = new BlocksBuilder(
            $layoutService
        );
        return $blocksBuilder;
    }
}
