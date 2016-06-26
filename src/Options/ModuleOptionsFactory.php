<?php

namespace ConLayout\Options;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ModuleOptionsFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ModuleOptions::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return ModuleOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $options = isset($config['con-layout']) ? $config['con-layout'] : [];

        if (!isset($options['controller_map'])
            && isset($config['view_manager']['controller_map'])
        ) {
            $options['controller_map'] = (array) $config['view_manager']['controller_map'];
        }

        $moduleOptions = new ModuleOptions(
            $options
        );
        return $moduleOptions;
    }
}
