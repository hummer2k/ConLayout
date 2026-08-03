<?php

declare(strict_types=1);

namespace ConLayout\Middleware;

use ConLayout\Options\ModuleOptions;
use ConLayout\View\Helper\BodyClass;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;

class BodyClassMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): BodyClassMiddleware {
        /** @var HelperPluginManager $helperPluginManager */
        $helperPluginManager = $container->get('ViewHelperManager');
        $bodyClassHelper = $helperPluginManager->get('bodyClass');

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        return new BodyClassMiddleware(
            $bodyClassHelper,
            $moduleOptions->getBodyClassPrefix()
        );
    }
}
