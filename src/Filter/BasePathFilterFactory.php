<?php

declare(strict_types=1);

namespace ConLayout\Filter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BasePathFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): BasePathFilter
    {
        $basePathHelper = $container
            ->get('ViewHelperManager')
            ->get('basePath');
        return new BasePathFilter($basePathHelper);
    }
}
