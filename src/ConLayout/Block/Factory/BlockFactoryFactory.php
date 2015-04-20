<?php

namespace ConLayout\Block\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactoryFactory implements FactoryInterface
{
    use \ConLayout\OptionTrait;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $blockFactory = new BlockFactory(
            $this->getOption($config, 'con-layout/block_factory/defaults', [])
        );
        return $blockFactory;
    }
}