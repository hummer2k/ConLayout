<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use ConLayout\LayoutManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LayoutFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Layout
    {
        return new Layout(
            $container->get(LayoutManager::class),
        );
    }
}
