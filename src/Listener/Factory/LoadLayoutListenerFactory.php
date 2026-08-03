<?php

declare(strict_types=1);

namespace ConLayout\Listener\Factory;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LoadLayoutListener;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LoadLayoutListenerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LoadLayoutListener
    {
        $injectBlocksListener = new LoadLayoutListener(
            $container->get(LayoutInterface::class)
        );
        return $injectBlocksListener;
    }
}
