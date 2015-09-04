<?php
namespace ConLayoutTest\Listener;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Updater\LayoutUpdater;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerTest extends AbstractTest
{
    public function testActionHandles()
    {
        $updater = new LayoutUpdater();

        $this->assertEquals([
            'default'
        ], $updater->getHandles());

        $actionHandlesListener = new ActionHandlesListener(
            $updater
        );
        $event = new MvcEvent();
        $routeMatch = new RouteMatch([
            'controller' => 'App\Controller\Index',
            'action' => 'index'
        ]);
        $event->setRouteMatch($routeMatch);

        $actionHandlesListener->addActionHandles($event);

        $this->assertEquals([
            'default',
            'app',
            'app-index',
            'app-index-index'
        ], $updater->getHandles());

    }
    
    /**
     * @see https://github.com/hummer2k/ConLayout/issues/4
     */
    public function testActionHandlesWithCamelCase()
    {
        $updater = new LayoutUpdater();
    
        $this->assertEquals([
            'default'
        ], $updater->getHandles());
    
        $actionHandlesListener = new ActionHandlesListener($updater);
        $event = new MvcEvent();
    
        $routeMatch = new RouteMatch([
            'controller' => 'SomeModule\HomeController\Index',
            'action' => 'index'
        ]);
    
        $event->setRouteMatch($routeMatch);
        $actionHandlesListener->addActionHandles($event);
    
        $this->assertEquals([
            'default',
            'some-module',
            'some-module-home',
            'some-module-home-index'
        ], $updater->getHandles());
    }

    public function testAttach()
    {
        $eventManager = new EventManager();
        $listener = new ActionHandlesListener(
            $this->getMock('ConLayout\Updater\LayoutUpdaterInterface')
        );

        $listener->attach($eventManager);
        $listeners = $eventManager->getListeners(MvcEvent::EVENT_DISPATCH);

        foreach ($listeners as $attachedListener) {
            $this->assertTrue($attachedListener->getCallback()[0] === $listener);
        }
    }
}
