<?php

namespace ConLayout\AssetPreparer;

use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBusterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $internalBaseDir = $options->getCacheBusterInternalBaseDir();
        $cacheBuster = new CacheBuster($internalBaseDir);
        return $cacheBuster;
    }
}
