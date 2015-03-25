<?php

namespace ConLayoutTest;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheFactoryTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new \ConLayout\CacheFactory();
        $this->sm->setAllowOverride(true);
        $config = $this->sm->get('Config');
        
        $config = \Zend\Stdlib\ArrayUtils::merge($config, [
            'con-layout' => [
                'cache_dir' => 'does-not-exist'
            ]
        ]);

        $this->sm->setService('Config', $config);
        $cache = $factory->createService($this->sm);

        $this->assertInstanceOf('Zend\Cache\Storage\StorageInterface', $cache);
    }
}