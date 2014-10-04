<?php
namespace ConLayout;

use Zend\ServiceManager\FactoryInterface;

/**
 * Factory
 *
 * @author hummer 
 */
class CacheFactory
    implements FactoryInterface
{
    use OptionTrait;
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $cacheDir = $this->getOption($config, 'con-layout/cache_dir', './data/cache/con-layout');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cache   = \Zend\Cache\StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'redis',
                'options' => array(
                    'server' => array(
                        'host' => '127.0.0.1',
                        'port' => 6379
                    )
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
