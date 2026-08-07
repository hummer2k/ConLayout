<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FilesystemCollectorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): FilesystemCollector
    {
        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);
        $paths = $options->getLayoutUpdatePaths();
        $extensions = $options->getLayoutUpdateExtensions();
        $collector = new FilesystemCollector(
            $paths,
            $extensions,
            $container->get(XmlPreprocessor::class),
        );
        return $collector;
    }
}
