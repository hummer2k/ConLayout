<?php

namespace ConLayout\Updater;

use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\FilesystemCollector;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $updater = new LayoutUpdater();
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);
        foreach ($moduleOptions->getCollectors() as $name => $collector) {
            $updater->attachCollector(
                $name,
                $serviceLocator->get($collector['class']),
                $collector['priority']
            );
        }
        return $updater;
    }
}
