<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BlocksGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BlocksGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $blocksGenerator = new BlocksGenerator(
            $container->get(BlockFactoryInterface::class),
            $container->get(BlockPoolInterface::class)
        );
        return $blocksGenerator;
    }
}
