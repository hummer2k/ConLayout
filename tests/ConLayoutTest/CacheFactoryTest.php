<?php

namespace ConLayoutTest;

use ConLayout\CacheFactory;
use Zend\Stdlib\ArrayUtils;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheFactoryTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new CacheFactory();
        $this->sm->setAllowOverride(true);
        $config = $this->sm->get('Config');
        
        $config = ArrayUtils::merge($config, [
            'con-layout' => [
                'cache_dir' => __DIR__ . '/_files/cache'
            ]
        ]);

        $this->sm->setService('Config', $config);
        $cache = $factory->createService($this->sm);

        $this->assertInstanceOf('Zend\Cache\Storage\StorageInterface', $cache);
    }
}