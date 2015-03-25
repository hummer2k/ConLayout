<?php

namespace ConLayoutTest;

use ConLayout\Module;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getAutoloaderConfig());
        $this->assertInternalType('array', $module->getConfig());
        $this->assertInternalType('array', $module->getServiceConfig());

    }

    public function testOnBootstrap()
    {
        
    }
}
