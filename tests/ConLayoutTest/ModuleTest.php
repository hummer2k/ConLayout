<?php

namespace ConLayoutTest;

use ConLayout\Module;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Helper\BodyClass;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManager;
use Zend\Filter\FilterPluginManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

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
        $this->assertInternalType('array', $module->getFilterConfig());
    }

    public function testServicesAndFactories()
    {
        $sm = Bootstrap::getServiceManager();
        $module = new Module();

        $services = $module->getServiceConfig();

        foreach (['invokables', 'factories'] as $type) {
            foreach (array_keys($services[$type]) as $name) {
                $instance = $sm->get($name);
                $this->assertInstanceOf(
                    $name,
                    $instance
                );
            }
        }
    }

    public function testOnBootstrapListenersWithHttpRequest()
    {
        $module = new Module();

        $application = $this->createApplication();

        $sm = $application->getServiceManager();
        $sm->setService('FilterManager', new FilterPluginManager);

        foreach ($module->getServiceConfig()['invokables'] as $key => $value) {
            $sm->setInvokableClass($key, $value);
        }
        foreach ($module->getServiceConfig()['factories'] as $key => $value) {
            $sm->setFactory($key, $value);
        }

        $sm->get('ViewHelperManager')->setService(
            'bodyClass',
            $this->getMock(BodyClass::class)
        );

        $event = new MvcEvent();
        $event->setApplication($application);
        $em = $application->getEventManager();

        $em->getSharedManager()->clearListeners(LayoutUpdater::class);

        $this->assertCount(0, $em->getListeners(MvcEvent::EVENT_DISPATCH));
        $this->assertCount(0, $em->getListeners(MvcEvent::EVENT_RENDER));

        $module->onBootstrap($event);

        $this->assertCount(4, $em->getListeners(MvcEvent::EVENT_DISPATCH));

        $layoutUpdater = $sm->get(LayoutUpdaterInterface::class);

        $this->assertEquals([
            'default'
        ], $layoutUpdater->getHandles());

        $mvcEvent = new MvcEvent();
        $mvcEvent->setApplication($application);
        $mvcEvent->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $mvcEvent->setError('test-error');

        $em->trigger($mvcEvent);

        $this->assertEquals([
            'default',
            'test-error'
        ], $layoutUpdater->getHandles());

    }

    public function testOnBootstrapListenersWithConsoleRequest()
    {
        $module = new Module();

        $application = $this->createApplication();

        $sm = $application->getServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService('Request', new ConsoleRequest());

        $em = $application->getEventManager();

        $event = new MvcEvent();
        $event->setApplication($application);

        $this->assertCount(0, $em->getListeners(MvcEvent::EVENT_DISPATCH));

        $module->onBootstrap($event);

        $this->assertCount(0, $em->getListeners(MvcEvent::EVENT_DISPATCH));

    }

    protected function createApplication()
    {
        $sm = new ServiceManager();
        $sm->setService('EventManager', new EventManager());
        $sm->setService('Request', new Request());
        $sm->setService('Response', new Response());
        $sm->setService('Config', Bootstrap::getConfig());
        $sm->setService('ViewHelperManager', new HelperPluginManager());
        $application = new Application([], $sm);
        return $application;
    }

    public function testInit()
    {
        $module = new Module();
        $manager = Bootstrap::getServiceManager()->get('ModuleManager');
        $module->init($manager);
    }

    public function testViewHelperConfig()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getViewHelperConfig());
    }
}
