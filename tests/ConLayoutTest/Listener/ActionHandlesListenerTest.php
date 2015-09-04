<?php
namespace ConLayoutTest\Listener;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Updater\LayoutUpdater;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Controller\TestAsset\SampleController;
use Zend\EventManager\EventManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerTest extends AbstractTest
{
    public function setUp()
    {
        $controllerMap = [
            'MappedNs' => true,
            'ZendTest\MappedNs' => true,
        ];
        
        $this->updater = new LayoutUpdater;
        $this->listener = new ActionHandlesListener;
        $this->listener->setUpdater($this->updater);
        $this->listener->setControllerMap($controllerMap);
        $this->event = new MvcEvent;
        $this->routeMatch = new RouteMatch([]);
        $this->event->setRouteMatch($this->routeMatch);
    }
    
    /**
     * @covers ActionHandlesListener::<protected>
     */
    public function testUsesModuleAndControllerOnlyIfNoActionInRouteMatch()
    {
        $this->routeMatch->setParam('controller', 'Foo\Controller\SomewhatController');
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'foo',
            'foo-somewhat'
        ], $this->updater->getHandles());
    }
    
    public function testNormalizesLiteralControllerNameIfNoNamespaceSeparatorPresent()
    {
        $this->routeMatch->setParam('controller', 'SomewhatController');
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'somewhat'
        ], $this->updater->getHandles());
    }
    
    public function testNormalizesNamesToLowercase()
    {
        $this->routeMatch->setParam('controller', 'Somewhat.DerivedController');
        $this->routeMatch->setParam('action', 'some-UberCool');
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'somewhat.derived',
            'somewhat.derived-some',
            'somewhat.derived-some-uber',
            'somewhat.derived-some-uber-cool'
        ], $this->updater->getHandles());
    }
    
    public function testMapsSubNamespaceToSubDirectoryWithControllerFromRouteMatch()
    {
        $this->routeMatch->setParam(ModuleRouteListener::MODULE_NAMESPACE, 'Aj\Controller\SweetAppleAcres\Reports');
        $this->routeMatch->setParam('controller', 'CiderSales');
        $this->routeMatch->setParam('action', 'PinkiePieRevenue');
        $moduleRouteListener = new ModuleRouteListener;
        $moduleRouteListener->onRoute($this->event);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'aj',
            'aj-sweet',
            'aj-sweet-apple',
            'aj-sweet-apple-acres',
            'aj-sweet-apple-acres-reports',
            'aj-sweet-apple-acres-reports-cider',
            'aj-sweet-apple-acres-reports-cider-sales',
            'aj-sweet-apple-acres-reports-cider-sales-pinkie',
            'aj-sweet-apple-acres-reports-cider-sales-pinkie-pie',
            'aj-sweet-apple-acres-reports-cider-sales-pinkie-pie-revenue'
        ], $this->updater->getHandles());
    }
    
    public function testMapsSubNamespaceToSubDirectoryWithControllerFromRouteMatchHavingSubNamespace()
    {
        $this->routeMatch->setParam(ModuleRouteListener::MODULE_NAMESPACE, 'Aj\Controller\SweetAppleAcres\Reports');
        $this->routeMatch->setParam('controller', 'Sub\CiderSales');
        $this->routeMatch->setParam('action', 'PinkiePieRevenue');
        $moduleRouteListener = new ModuleRouteListener;
        $moduleRouteListener->onRoute($this->event);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'aj',
            'aj-sweet',
            'aj-sweet-apple',
            'aj-sweet-apple-acres',
            'aj-sweet-apple-acres-reports',
            'aj-sweet-apple-acres-reports-cider',
            'aj-sweet-apple-acres-reports-cider-sales',
            'aj-sweet-apple-acres-reports-cider-sales-pinkie',
            'aj-sweet-apple-acres-reports-cider-sales-pinkie-pie', 
            'aj-sweet-apple-acres-reports-cider-sales-pinkie-pie-revenue'
        ], $this->updater->getHandles());
    }
    
    public function testMapsSubNamespaceToSubDirectoryWithControllerFromEventTarget()
    {
        $this->routeMatch->setParam(ModuleRouteListener::MODULE_NAMESPACE, 'ConLayoutTest\Controller\TestAsset');
        $this->routeMatch->setParam('action', 'test');
        $moduleRouteListener = new ModuleRouteListener;
        $moduleRouteListener->onRoute($this->event);
        $myController = new SampleController();
        $this->event->setTarget($myController);;
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'con',
            'con-layout',
            'con-layout-test',
            'con-layout-test-test',
            'con-layout-test-test-asset',
            'con-layout-test-test-asset-sample',
            'con-layout-test-test-asset-sample-test'
        ], $this->updater->getHandles());
    }
    
    public function testMapsSubNamespaceToSubDirectoryWithControllerFromEventTargetShouldMatchControllerFromRouteParam()
    {
        $this->routeMatch->setParam(ModuleRouteListener::MODULE_NAMESPACE, 'ConLayoutTest\Controller');
        $this->routeMatch->setParam('controller', 'TestAsset\SampleController');
        $this->routeMatch->setParam('action', 'test');
        $moduleRouteListener = new ModuleRouteListener;
        $moduleRouteListener->onRoute($this->event);
        $this->listener->injectActionHandles($this->event);
        $handles1 = $this->updater->getHandles();
        
        $myController = new SampleController();
        $this->event->setTarget($myController);
        $this->listener->injectActionHandles($this->event);
        $handles2 = $this->updater->getHandles();
        
        $this->assertEquals($handles1, $handles2);
    }
    
    public function testControllerMatchedByMapIsInflected1()
    {
        $controller = 'MappedNs\SubNs\Controller\Sample';
        $this->routeMatch->setParam('controller', $controller);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'mapped',
            'mapped-ns',
            'mapped-ns-sub',
            'mapped-ns-sub-ns',
            'mapped-ns-sub-ns-sample'
        ], $this->updater->getHandles());
    
        $this->updater = new LayoutUpdater;
        $this->listener = new ActionHandlesListener;
        $this->listener->setUpdater($this->updater);
    
        $this->listener->setControllerMap(['ZendTest' => true]);
        $myController = new SampleController();
        $this->event->setTarget($myController);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'con',
            'con-layout',
            'con-layout-test',
            'con-layout-test-sample'
        ], $this->updater->getHandles());
    }
    
    public function testControllerNotMatchedByMapIsNotAffected()
    {
        $this->routeMatch->setParam('action', 'test');
        $myController = new SampleController();
        $this->event->setTarget($myController);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'con',
            'con-layout',
            'con-layout-test',
            'con-layout-test-sample',
            'con-layout-test-sample-test'
        ], $this->updater->getHandles());
    }
    
    public function testFullControllerNameMatchIsMapped()
    {
        $this->listener->setControllerMap([
            'Foo\Bar\Controller\IndexController' => 'string-value',
        ]);
        $controller = 'Foo\Bar\Controller\IndexController';
        $actionHandle = $this->listener->mapController('Foo\Bar\Controller\IndexController');
        $this->assertEquals('string-value', $actionHandle);
        $this->routeMatch->setParam('controller', $controller);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'string',
            'string-value'
        ], $this->updater->getHandles());
    }
    
    public function testOnlyFullNamespaceMatchIsMapped()
    {
        $this->listener->setControllerMap([
            'Foo' => 'foo-matched',
            'Foo\Bar' => 'foo-bar-matched',
        ]);
        $controller = 'Foo\BarBaz\Controller\IndexController';
        $actionHandle = $this->listener->mapController($controller);
        $this->assertEquals('foo-matched-bar-baz-index', $actionHandle);
        $this->routeMatch->setParam('controller', $controller);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'foo',
            'foo-matched',
            'foo-matched-bar',
            'foo-matched-bar-baz',
            'foo-matched-bar-baz-index'
        ], $this->updater->getHandles());
    }
    
    public function testControllerMapMatchedPrefixReplacedByStringValue()
    {
        $this->listener->setControllerMap([
            'Foo\Bar' => 'string_value',
        ]);
        $controller = 'Foo\Bar\Controller\IndexController';
        $actionHandle = $this->listener->mapController($controller);
        $this->assertEquals('string_value-index', $actionHandle);
        $this->routeMatch->setParam('controller', $controller);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'string_value',
            'string_value-index'
        ], $this->updater->getHandles());
    }
    
    public function testUsingNamespaceRouteParameterGivesSameResultAsFullControllerParameter()
    {
        $controller1 = 'MappedNs\Foo\Controller\Bar\Baz\Sample';
        $this->routeMatch->setParam('controller', $controller1);
        $this->listener->injectActionHandles($this->event);
        $handles1 = $this->updater->getHandles();
    
        $controller2 = 'MappedNs\Foo\Controller\Bar';
        $this->routeMatch->setParam(ModuleRouteListener::MODULE_NAMESPACE, $controller2);
        $this->routeMatch->setParam('controller', 'Baz\Sample');
        $moduleRouteListener = new ModuleRouteListener;
        $moduleRouteListener->onRoute($this->event);
        $this->listener->injectActionHandles($this->event);
        $handles2 = $this->updater->getHandles();
        
        $this->assertEquals($handles1, $handles2);
    }
    
    public function testControllerMapOnlyFullNamespaceMatches()
    {
        $this->listener->setControllerMap([
            'Foo' => 'foo-matched',
            'Foo\Bar' => 'foo-bar-matched',
        ]);
        $controller = 'Foo\BarBaz\Controller\IndexController';
        $actionHandle = $this->listener->mapController($controller);
        $this->assertEquals('foo-matched-bar-baz-index', $actionHandle);
        $this->routeMatch->setParam('controller', $controller);
        $this->routeMatch->setParam('action', 'test');
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'foo',
            'foo-matched',
            'foo-matched-bar',
            'foo-matched-bar-baz',
            'foo-matched-bar-baz-index',
            'foo-matched-bar-baz-index-test'
        ], $this->updater->getHandles());
    }
    
    public function testControllerMapRuleSetToFalseIsIgnored()
    {
        $this->listener->setControllerMap([
            'Foo' => 'foo-matched',
            'Foo\Bar' => false,
        ]);
        $controller = 'Foo\Bar\Controller\IndexController';
        $actionHandle = $this->listener->mapController($controller);
        $this->assertEquals('foo-matched-bar-index', $actionHandle);
        $this->routeMatch->setParam('controller', $controller);
        $this->routeMatch->setParam('action', 'test');
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'foo',
            'foo-matched',
            'foo-matched-bar',
            'foo-matched-bar-index',
            'foo-matched-bar-index-test'
        ], $this->updater->getHandles());
    }
    
    public function testControllerMapMoreSpecificRuleMatchesFirst()
    {
        $this->listener->setControllerMap([
            'Foo'     => true,
            'Foo\Bar' => 'bar-baz',
        ]);
        $actionHandle = $this->listener->mapController('Foo\Bar\Controller\IndexController');
        $this->assertEquals('bar-baz-index', $actionHandle);
        $this->listener->setControllerMap([
            'Foo\Bar' => 'bar-baz',
            'Foo'     => true,
        ]);
        $actionHandle = $this->listener->mapController('Foo\Bar\Controller\IndexController');
        $this->assertEquals('bar-baz-index', $actionHandle);
    }
    
    public function testPrefersRouteMatchController()
    {
        $this->assertFalse($this->listener->isPreferRouteMatchController());
        $this->listener->setPreferRouteMatchController(true);
        $this->routeMatch->setParam('controller', 'Some\Other\Service\Namespace\Controller\Sample');
        $myController = new SampleController();
        $this->event->setTarget($myController);
        $this->listener->injectActionHandles($this->event);
        $this->assertEquals([
            'default',
            'some',
            'some-sample'
        ], $this->updater->getHandles());
    }
    
    public function testLayoutUpdaterContainsOnlyDefaultHandle()
    {
        $this->assertEquals(['default'], $this->updater->getHandles());
    }
    
    public function testListenerAttachesDispatchErrorEventAtExpectedPriority()
    {
        $events = new EventManager();
        $events->attachAggregate($this->listener);
        $listeners = $events->getListeners(MvcEvent::EVENT_DISPATCH_ERROR);
        $expectedCallback = [$this->listener, 'injectErrorHandle'];
        $expectedPriority = 100;
        $found            = false;
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();
            if ($callback === $expectedCallback) {
                if ($listener->getMetadatum('priority') == $expectedPriority) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Listener not found');
    }
    
    public function testListenerAttachesDispatchEventAtExpectedPriority()
    {
        $events = new EventManager();
        $events->attachAggregate($this->listener);
        $listeners = $events->getListeners(MvcEvent::EVENT_DISPATCH);
        $expectedCallback = [$this->listener, 'injectActionHandles'];
        $expectedPriority = 1000;
        $found            = false;
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();
            if ($callback === $expectedCallback) {
                if ($listener->getMetadatum('priority') == $expectedPriority) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found, 'Listener not found');
    }
    
    public function testGetterAndSetters()
    {
        $updater = new LayoutUpdater;
        $this->listener->setUpdater($updater);
        $this->assertEquals($updater, $this->listener->getUpdater());
    
        $controllerMap = [
            'Some/Name' => true,
            'Other/Name' => 'string'
        ];
        $this->listener->setControllerMap($controllerMap);
        $this->assertEquals($controllerMap, $this->listener->getControllerMap());
    
        $preferRouteMatchController = true;
        $this->listener->setPreferRouteMatchController($preferRouteMatchController);
        $this->assertEquals($preferRouteMatchController, $this->listener->isPreferRouteMatchController());
    }
}
