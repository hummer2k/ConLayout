<?php

declare(strict_types=1);

namespace ConLayout\Layout;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdaterInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Layout
    {
        $layout = new Layout(
            $container->get(LayoutUpdaterInterface::class),
            $container->get(BlockPoolInterface::class)
        );
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        foreach ($moduleOptions->getGenerators() as $name => $specs) {
            $generator = $container->get($specs['class']);
            $layout->attachGenerator($name, $generator, $specs['priority']);
        }
        return $layout;
    }
}
