<?php
namespace ConLayout\Config;

use ConLayout\OptionTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return Collector
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
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
