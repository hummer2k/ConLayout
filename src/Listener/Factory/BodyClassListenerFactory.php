<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\BodyClassListener;
use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListenerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return BodyClassListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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
