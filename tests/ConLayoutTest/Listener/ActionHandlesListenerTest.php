<?php
namespace ConLayoutTest\Listener;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Updater\LayoutUpdater;
use ConLayoutTest\AbstractTest;
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
        $event = new \Zend\Mvc\MvcEvent();
        $routeMatch = new RouteMatch([
            'controller' => 'App\Controller\Index',
            'action' => 'index'
        ]);
        $event->setRouteMatch($routeMatch);

        $actionHandlesListener->addActionHandles($event);

        $this->assertEquals([
            'default',
            'app',
            'app-controller',
            'app-controller-index',
            'app-controller-index-index'
        ], $updater->getHandles());

    }
}
