<?php

declare(strict_types=1);

namespace ConLayout\Updater;

use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutUpdaterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LayoutUpdater
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
