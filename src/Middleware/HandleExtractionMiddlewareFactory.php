<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Options\ModuleOptions;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class HandleExtractionMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): HandleExtractionMiddleware {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        return new HandleExtractionMiddleware(
            new CamelCaseToDash(),
            $moduleOptions->getErrorHandleAttribute(),
        );
    }
}
