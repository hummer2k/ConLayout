<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigCollectorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ConfigCollector::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return ConfigCollector
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $configStructure = isset($config['layout_updates'])
            ? (array) $config['layout_updates']
            : [];
        return new ConfigCollector($configStructure);
    }
}
