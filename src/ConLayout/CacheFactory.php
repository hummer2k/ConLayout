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
        $cache   = \Zend\Cache\StorageFactory::factory(array(
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
