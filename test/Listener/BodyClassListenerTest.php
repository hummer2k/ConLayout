<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\BodyClassListener;
use ConLayout\View\Helper\BodyClass;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\Router\Http\RouteMatch;

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
}
