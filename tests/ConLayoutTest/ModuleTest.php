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

    public function testOnBootstrap()
    {
        return;
        $module = new Module();

        $mvcEvent = new \Zend\Mvc\MvcEvent();
        
        $serviceManager = clone $this->sm;
        $serviceManager->setAllowOverride(true);
        $eventManager = new EventManager();
        $serviceManager->setService('EventManager', $eventManager);
        $serviceManager->setService('Request', new Request());
        $serviceManager->setService('Response', new Response());
        $application = new Application([], $serviceManager);

        $mvcEvent->setApplication($application);

        $module->onBootstrap($mvcEvent);

        $this->assertCount(1, $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR));
        $this->assertCount(3, $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_RENDER));
        $this->assertCount(2, $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH));

        $mvcEvent->setError('error-test');

        $layoutService = $serviceManager->get('ConLayout\Service\LayoutService')->reset();
        $eventManager->trigger(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, $mvcEvent);

        $handles = $layoutService->getHandles();

        $this->assertEquals([
            'default',
            'error-test'
        ], $handles);

    }

    public function testOnBootstrapConsole()
    {
        return;
        $module = new Module();

        $mvcEvent = new \Zend\Mvc\MvcEvent();

        $serviceManager = clone $this->sm;
        $serviceManager->setAllowOverride(true);
        $eventManager = new EventManager();
        $serviceManager->setService('EventManager', $eventManager);
        $application = new Application([], $serviceManager);

        $mvcEvent->setApplication($application);

        $module->onBootstrap($mvcEvent);

        $this->assertCount(0, $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR));
        $this->assertCount(0, $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_RENDER));
        $this->assertCount(0, $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH));

    }
}
