<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BlocksGeneratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $container)
    {
        $blocksGenerator = new BlocksGenerator(
            $container->get(BlockFactoryInterface::class),
            $container->get(BlockPoolInterface::class)
        );
        return $blocksGenerator;
    }
}
