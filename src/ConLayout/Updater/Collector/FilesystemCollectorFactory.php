<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;


use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FilesystemCollectorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get(ModuleOptions::class);
        $paths = $options->getLayoutUpdatePaths();
        $extensions = $options->getLayoutUpdateExtensions();
        $collector = new FilesystemCollector(
            $paths,
            $extensions
        );
        return $collector;
    }
}
