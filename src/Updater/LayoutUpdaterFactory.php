<?php

namespace ConLayout\Updater;

use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterFactory implements FactoryInterface
{
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
