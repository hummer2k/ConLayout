<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\BodyClassListenerFactory;
use ConLayoutTest\AbstractTest;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListenerTest extends AbstractTest
{
    public function testFactory()
    {
        $bodyClassListener = $this->createBodyClassListener();
        $this->assertInstanceOf('ConLayout\Listener\BodyClassListener', $bodyClassListener);
    }

    protected function createBodyClassListener()
    {
        $factory = new BodyClassListenerFactory();
        $bodyClassListener = $factory->createService($this->sm);
        return $bodyClassListener;
    }


    public function testAttach()
    {
        $eventManager = new \Zend\EventManager\EventManager();

        $bodyClassListener = $this->createBodyClassListener();

        $bodyClassListener->attach($eventManager);
        $routeMatch = new \Zend\Mvc\Router\Http\RouteMatch(array());
        $routeMatch->setMatchedRouteName('my/ROUTE/name');

        $event = new \Zend\Mvc\MvcEvent();
        $event->setRouteMatch($routeMatch);

        $eventManager->trigger(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, $event);

        /* @var $helper \ConLayout\View\Helper\BodyClass */
        $helper = $this->sm->get('viewHelperManager')->get('bodyClass');

        $this->assertEquals('my-route-name', (string) $helper);
    }
}