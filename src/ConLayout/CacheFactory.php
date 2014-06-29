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
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $cache   = \Zend\Cache\StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array(
                    'cache_dir' => './data/cache/con-layout'
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
