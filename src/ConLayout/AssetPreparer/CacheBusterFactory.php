<?php

namespace ConLayout\AssetPreparer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBusterFactory implements FactoryInterface
{
    use \ConLayout\OptionTrait;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $internalBaseDir = $this->getOption($config, 'con-layout/cache_buster/internal_base_dir', './public');
        $cacheBuster = new CacheBuster($internalBaseDir);
        return $cacheBuster;
    }
}