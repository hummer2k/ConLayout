<?php

namespace ConLayout\Filter;

use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBusterFilterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();
        /* @var $options ModuleOptions */
        $options = $serviceManager->get('ConLayout\Options\ModuleOptions');
        $internalBaseDir = $options->getCacheBusterInternalBaseDir();
        $cacheBuster = new CacheBusterFilter($internalBaseDir);
        return $cacheBuster;
    }
}
