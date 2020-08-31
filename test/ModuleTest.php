<?php

namespace ConLayoutTest;

use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\DebugFilterFactory;
use ConLayout\Module;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Helper\BodyClass;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\Filter\FilterPluginManager;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\View\Http\InjectTemplateListener;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;

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
        $phpRenderer = new PhpRenderer();
        $container = $application->getServiceManager();
        $filterManager = new FilterPluginManager($container);
        $filterManager->setFactory(ContainerFilter::class, DebugFilterFactory::class);


        $container->setService('FilterManager', $filterManager);
        $container->setService(InjectTemplateListener::class, new InjectTemplateListener());
        $container->setService(PhpRenderer::class, $phpRenderer);

        foreach ($module->getServiceConfig()['invokables'] as $key => $value) {
            $container->setInvokableClass($key, $value);
        }
        foreach ($module->getServiceConfig()['factories'] as $key => $value) {
            $container->setFactory($key, $value);
        }

        $container->get('ViewHelperManager')->setService(
            'bodyClass',
            $this->getMockBuilder(BodyClass::class)->getMock()
        );

        $event = new MvcEvent();
        $event->setApplication($application);
        $em = $application->getEventManager();

        $em->getSharedManager()->clearListeners(LayoutUpdater::class);

        $module->onBootstrap($event);

        $layoutUpdater = $container->get(LayoutUpdaterInterface::class);

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
