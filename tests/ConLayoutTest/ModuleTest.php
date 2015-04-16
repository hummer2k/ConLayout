<?php

namespace ConLayoutTest;

use ConLayout\Module;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Application;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ModuleTest extends AbstractTest
{
    public function testConfig()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getAutoloaderConfig());
        $this->assertInternalType('array', $module->getConfig());
        $this->assertInternalType('array', $module->getServiceConfig());
    }

    public function testServicesAndFactories()
    {
        $sm = Bootstrap::getServiceManager();
        $module = new Module();

        $services = $module->getServiceConfig();

        foreach(['invokables', 'factories'] as $type) {
            foreach (array_keys($services[$type]) as $name) {
                $instance = $sm->get($name);
                $this->assertInstanceOf(
                    $name,
                    $instance
                );
            }
        }
    }
}
