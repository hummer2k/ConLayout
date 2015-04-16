<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\BodyClassListener;
use ConLayout\View\Helper\BodyClass;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\Mvc\Router\Http\RouteMatch;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListenerTest extends AbstractTest
{

    public function testAddBodyClass()
    {
        $bodyClassHelper = new BodyClass();
        $listener = new BodyClassListener($bodyClassHelper);

        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('my/SOME/route');
        $event = new \Zend\Mvc\MvcEvent();
        $event->setRouteMatch($routeMatch);

        $listener->addBodyClass($event);

        $this->assertEquals('my-some-route', (string) $bodyClassHelper);
    }

    public function testAttach()
    {
        $eventManager = new EventManager();
        $listener = new BodyClassListener(new BodyClass());

        $before = $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH);
        $this->assertCount(0, $before);

        $eventManager->attach($listener);

        $after = $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH);
        $this->assertCount(1, $after);

    }
}