<?php
namespace ConLayout\Cache;

use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory
 *
 * @author hummer 
 */
class CacheFactory
    implements FactoryInterface
{
    use \ConLayout\OptionTrait;
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $cacheDir = $this->getOption($config, 'con-layout/cache_dir', './data/cache/con-layout');
        $cache   = StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array(
                    'cache_dir' => $cacheDir
                    )
            ),
            'plugins' => array(
                'serializer',
                'exception_handler' => array(
                    'throw_exceptions' => false
                ),
            )
        ));
        return $cache;
    }
}
