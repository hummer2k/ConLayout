<?php
namespace ConLayout\Service\Config;

use Zend\ServiceManager\FactoryInterface;

/**
 * CollectorFactory
 *
 * @author hummer 
 */
class CollectorFactory
    implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Config\Collector
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $globPath = isset($config['con-layout']['config_glob_path'])
            ? $config['con-layout']['config_glob_path']
            : './module/*/config/layout.config.php';
        $collector = new Collector($globPath);
        return $collector;
    }
}
