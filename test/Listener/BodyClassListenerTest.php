<?php

declare(strict_types=1);

namespace ConLayoutTest\Listener;

use ConLayout\Listener\BodyClassListener;
use ConLayout\View\Helper\BodyClass;
use ConLayoutTest\AbstractTestCase;
use Laminas\Router\Http\RouteMatch;

class BodyClassListenerTest extends AbstractTestCase
{
    public function testAddBodyClass(): void
    {
        $bodyClassHelper = new BodyClass();
        $listener = new BodyClassListener($bodyClassHelper);

        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('my/SOME/route');
        $event = new \Laminas\Mvc\MvcEvent();
        $event->setRouteMatch($routeMatch);

        $listener->addBodyClass($event);

        $this->assertEquals('my-some-route', (string) $bodyClassHelper);
    }
}
