<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheBusterFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): CacheBusterFilter
    {
        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);
        $internalBaseDir = $options->getCacheBusterInternalBaseDir();
        $cacheBuster = new CacheBusterFilter($internalBaseDir);
        return $cacheBuster;
    }
}
