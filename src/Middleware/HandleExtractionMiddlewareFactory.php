<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Inflector\TemplateInflector;
use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HandleExtractionMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): HandleExtractionMiddleware {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        $inflector = new TemplateInflector();
        $inflector->setControllerMap($moduleOptions->getControllerMap());
        $inflector->setPreferRouteMatchController($moduleOptions->isPreferRouteMatchController());

        return new HandleExtractionMiddleware(
            $inflector,
            $moduleOptions->getErrorHandleAttribute()
        );
    }
}
