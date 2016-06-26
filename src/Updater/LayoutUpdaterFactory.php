<?php

namespace ConLayout\Updater;

use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, LayoutUpdater::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return LayoutUpdater
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $updater = new LayoutUpdater();
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        foreach ($moduleOptions->getCollectors() as $name => $collector) {
            $updater->attachCollector(
                $name,
                $container->get($collector['class']),
                $collector['priority']
            );
        }
        return $updater;
    }
}
