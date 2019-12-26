<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\BodyClassListener;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

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
        return new BodyClassListener(
            $bodyClassHelper
        );
    }
}
