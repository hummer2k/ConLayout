<?php

declare(strict_types=1);

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\BodyClassListener;
use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BodyClassListenerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): BodyClassListener
    {
        $bodyClassHelper = $container->get('ViewHelperManager')->get('bodyClass');
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions   = $container->get(ModuleOptions::class);
        return new BodyClassListener(
            $bodyClassHelper,
            $moduleOptions->getBodyClassPrefix()
        );
    }
}
