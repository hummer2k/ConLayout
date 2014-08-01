<?php
namespace ConLayout\Service\Config;

use Zend\ServiceManager\FactoryInterface,
    ConLayout\OptionTrait;

/**
 * CollectorFactory
 *
 * @author hummer 
 */
class CollectorFactory
    implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Config\Collector
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $globPath = $this->getOption($config, 'con-layout/config_glob_paths', array());
        if (!is_array($globPath)) {
            $globPath = array($globPath);
        }
        $collector = new Collector($globPath);
        return $collector;
    }
}
