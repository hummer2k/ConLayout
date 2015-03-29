<?php
namespace ConLayoutTest\Config;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CollectorTest extends \ConLayoutTest\AbstractTest
{
    public function testCollect()
    {
        $configs = $this->collector->collect();        
        $this->assertInternalType('array', $configs);        
        $this->assertCount(2, $configs);
    }

    public function testFactory()
    {
        $factory = new \ConLayout\Config\CollectorFactory();
        $serviceManager = clone $this->sm;
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', [
            'con-layout' => [
                'config_glob_paths' => '/some/path/*.layout.php'
            ]
        ]);
        $collector = $factory->createService($serviceManager);
        $this->assertInstanceOf('ConLayout\Config\CollectorInterface', $collector);
    }

    public function testArea1()
    {
        $globPaths = [
            __DIR__ . '/../_config/{{area}}/layout.config.php'
        ];
        $collector = new \ConLayout\Config\Collector($globPaths);
        $collector->setArea('area_1');
        $configs = $collector->collect();

        $this->assertEquals([
            'default' => [
                'layout' => 'layout/area_1'
            ]
        ], current($configs));
    }

    public function testArea2()
    {
        $globPaths = [
            __DIR__ . '/../_config/{{area}}/layout.config.php'
        ];
        $collector = new \ConLayout\Config\Collector($globPaths, 'area_2');
        $configs = $collector->collect();

        $this->assertEquals([
            'default' => [
                'layout' => 'layout/area_2'
            ]
        ], current($configs));

        $this->assertEquals('area_2', $collector->getArea());
    }
}
