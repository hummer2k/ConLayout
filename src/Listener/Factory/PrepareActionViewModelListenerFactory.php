<?php

declare(strict_types=1);

namespace ConLayout\Listener\Factory;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Listener\PrepareActionViewModelListener;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PrepareActionViewModelListenerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): PrepareActionViewModelListener {
        return new PrepareActionViewModelListener(
            $container->get(BlockPoolInterface::class)
        );
    }
}
