<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\Block\BlockRegistryInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BlockFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Block
    {
        return new Block(
            $container->get(BlockRegistryInterface::class),
        );
    }
}
