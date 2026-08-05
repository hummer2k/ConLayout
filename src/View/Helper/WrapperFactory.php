<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\HtmlAttributes;
use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

class WrapperFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): Wrapper {
        return new Wrapper(
            $container->get(HelperPluginManager::class)->get(HtmlAttributes::class),
        );
    }
}
