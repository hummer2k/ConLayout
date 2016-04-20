<?php

namespace ConLayout\Block\Factory;

use ConLayout\BlockManager;
use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, BlockFactory::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return BlockFactory
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);
        $blockFactory = new BlockFactory(
            $options->getBlockDefaults(),
            $container->get(BlockManager::class),
            $container
        );
        return $blockFactory;
    }
}
