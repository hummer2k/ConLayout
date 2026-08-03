<?php

declare(strict_types=1);

namespace ConLayout\Block\Factory;

use ConLayout\BlockManager;
use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BlockFactoryFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): BlockFactory
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
