<?php

declare(strict_types=1);

namespace ConLayout\Generator;

use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ViewHelperGeneratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): ViewHelperGenerator
    {
        /** @var ModuleOptions $options */
        $options             = $container->get(ModuleOptions::class);
        $viewHelperManager   = $container->get('ViewHelperManager');
        $helperConfig        = $options->getViewHelpers();
        $viewHelperGenerator = new ViewHelperGenerator(
            $viewHelperManager,
            $helperConfig,
        );

        $viewHelperGenerator->setFilterManager($container);

        $viewHelperGenerator->setDebug($options->isDebug());

        return $viewHelperGenerator;
    }
}
