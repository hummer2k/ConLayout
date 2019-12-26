<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;

use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class FilesystemCollectorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return FilesystemCollector
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);
        $paths = $options->getLayoutUpdatePaths();
        $extensions = $options->getLayoutUpdateExtensions();
        $collector = new FilesystemCollector(
            $paths,
            $extensions
        );
        return $collector;
    }
}
