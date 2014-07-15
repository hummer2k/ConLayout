<?php
namespace ConLayoutTest\Service\Config;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CollectorTest extends \ConLayoutTest\AbstractTest
{
    /**
     * @covers \ConLayout\Service\Config\Collector::collect
     */
    public function testCollect()
    {
        $configs = $this->collector->collect();        
        $this->assertInternalType('array', $configs);        
        $this->assertEquals(2, count($configs));
    }
}
