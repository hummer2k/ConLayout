<?php

namespace ConLayoutTest;

use ConLayout\Module;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Helper\BodyClass;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Filter\FilterPluginManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ModuleManager\Listener\ServiceListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\InjectTemplateListener;
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
        $this->assertIsArray($module->getConfig());
        $this->assertIsArray($module->getServiceConfig());
        $this->assertIsArray($module->getFilterConfig());
    }

    public function testOnBootstrapListenersWithHttpRequest()
    {
        $module = new Module();

        $application = $this->createApplication();

        $sm = $application->getServiceManager();
        $sm->setService('FilterManager', new FilterPluginManager($sm));
        $sm->setService(InjectTemplateListener::class, new InjectTemplateListener());

        foreach ($module->getServiceConfig()['invokables'] as $key => $value) {
            $sm->setInvokableClass($key, $value);
        }
        foreach ($module->getServiceConfig()['factories'] as $key => $value) {
            $sm->setFactory($key, $value);
        }

        $sm->get('ViewHelperManager')->setService(
            'bodyClass',
            $this->getMockBuilder(BodyClass::class)->getMock()
        );

        $event = new MvcEvent();
        $event->setApplication($application);
        $em = $application->getEventManager();

        $em->getSharedManager()->clearListeners(LayoutUpdater::class);

        $module->onBootstrap($event);

        $layoutUpdater = $sm->get(LayoutUpdaterInterface::class);

        $this->assertEquals([
            'default'
        ], $layoutUpdater->getHandles());

        $mvcEvent = new MvcEvent();
        $mvcEvent->setApplication($application);
        $mvcEvent->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $mvcEvent->setError('test-error');

        $em->triggerEvent($mvcEvent);

        $this->assertEquals([
            'default',
            'test-error'
        ], $layoutUpdater->getHandles());
    }

    protected function createApplication()
    {
        $sm = new ServiceManager();
        $sm->setService('EventManager', new EventManager(new SharedEventManager()));
        $sm->setService('Request', new Request());
        $sm->setService('Response', new Response());
        $sm->setService('Config', []);
        $sm->setService('ViewHelperManager', new HelperPluginManager($this->sm));
        $application = new Application($sm);
        return $application;
    }

    public function testInit()
    {
        $module = new Module();
        /** @var ModuleManager $manager */
        $manager = Bootstrap::getServiceManager()->get('ModuleManager');
        $module->init($manager);

        $sm = Bootstrap::getServiceManager();

        $this->assertTrue($sm->has('BlockManager'));
    }

    public function testViewHelperConfig()
    {
        $module = new Module();
        $this->assertIsArray($module->getViewHelperConfig());
    }
}
