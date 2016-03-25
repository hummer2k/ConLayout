<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BlocksGeneratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, BlocksGenerator::class);
    }

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
