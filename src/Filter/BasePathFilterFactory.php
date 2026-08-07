<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Laminas\View\Helper\BasePath as BasePathHelper;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BasePathFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): BasePathFilter
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $basePath = (string) ($config['con-layout']['base_path'] ?? '');
        return new BasePathFilter(new BasePathHelper($basePath));
    }
}
