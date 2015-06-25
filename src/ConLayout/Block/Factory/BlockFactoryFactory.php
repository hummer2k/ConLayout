<?php

namespace ConLayout\Block\Factory;

use ConLayout\Options\ModuleOptions;
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
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $blockFactory = new BlockFactory(
            $options->getBlockDefaults(),
            $serviceLocator->get('ConLayout\BlockManager')
        );
        return $blockFactory;
    }
}
