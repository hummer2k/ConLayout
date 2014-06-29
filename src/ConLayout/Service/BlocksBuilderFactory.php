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
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $layoutConfig = $serviceLocator->get('ConLayout\Service\Config');
        $blocksBuilder = new BlocksBuilder(
            $layoutConfig->getBlockConfig()
        );
        return $blocksBuilder;
    }
}
