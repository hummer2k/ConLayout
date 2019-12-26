<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Filter;

use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheBusterFilterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return CacheBusterFilter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);
        $internalBaseDir = $options->getCacheBusterInternalBaseDir();
        $cacheBuster = new CacheBusterFilter($internalBaseDir);
        return $cacheBuster;
    }
}
