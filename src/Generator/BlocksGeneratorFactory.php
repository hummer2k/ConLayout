<?php

declare(strict_types=1);

namespace ConLayout\Generator;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BlocksGeneratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): BlocksGenerator
    {
        $blocksGenerator = new BlocksGenerator(
            $container->get(BlockFactoryInterface::class),
            $container->get(BlockPoolInterface::class),
        );
        return $blocksGenerator;
    }
}
