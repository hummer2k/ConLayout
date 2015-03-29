<?php
namespace ConLayoutTest\Listener;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Listener\ActionHandlesListenerFactory;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\Mvc\Router\Http\RouteMatch;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListenerTest extends AbstractTest
{
    public function testControllerHandles()
    {
        $routeMatch = new RouteMatch(array(
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ));
        $handlesListener = new ActionHandlesListener(
            ActionHandlesListener::BEHAVIOR_CONTROLLER,
            $this->layoutService->reset(),
            array()
        );
        
        $this->assertSame(
            $handlesListener->getActionHandles($routeMatch),
            array(
                'Application',
                'Application\Controller',
                'Application\Controller\Index',
                'Application\Controller\Index::index'
            )
        );
    }
    
    public function testRouteHandles()
    {
        $routeMatch = new RouteMatch(array(
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ));
        $routeMatch->setMatchedRouteName('user/login');
        $handlesListener = new ActionHandlesListener(
            ActionHandlesListener::BEHAVIOR_ROUTENAME,
            $this->layoutService->reset()
        );
        
        $this->assertSame(
            $handlesListener->getActionHandles($routeMatch),
            array(
                'user',
                'user/login'
            )
        );
    }

    public function testAddActionHandles()
    {
        $routeMatch = new RouteMatch(array(
            'controller' => 'User\Controller\Index',
            'action' => 'login'
        ));
        $event = new \Zend\Mvc\MvcEvent();
        $event->setRouteMatch($routeMatch);

        $handlesListener = new ActionHandlesListener(
            ActionHandlesListener::BEHAVIOR_COMBINED,
            $this->layoutService->reset()
        );

        $handlesListener->addActionHandles($event);

        $this->assertEquals(array(
            'default',
            'User',
            'User\Controller',
            'User\Controller\Index',
            'User\Controller\Index::login'
        ), $this->layoutService->getHandles());
    }

    public function testSettersAndGetters()
    {
        $handlesListener = new ActionHandlesListener(
            ActionHandlesListener::BEHAVIOR_COMBINED,
            $this->layoutService->reset()
        );
        $this->assertInstanceOf('ConLayout\Service\LayoutService', $handlesListener->getLayoutService());

        $handlesListener->setRouteSeparator('_');
        $this->assertSame('_', $handlesListener->getRouteSeparator());

    }

    public function testCombinedHandles()
    {
        $routeMatch = new RouteMatch(array(
            'controller' => 'User\Controller\Index',
            'action' => 'login'
        ));
        $routeMatch->setMatchedRouteName('user/login');
        $handlesListener = new ActionHandlesListener(
            ActionHandlesListener::BEHAVIOR_COMBINED,
            $this->layoutService->reset()
        );
        
        $this->assertSame(
            array_values($handlesListener->getActionHandles($routeMatch)),
            array(
                'user',
                'user/login',
                'User',
                'User\Controller',
                'User\Controller\Index',
                'User\Controller\Index::login'
            )
        );
       
    }

    public function testAttach()
    {
        $handlesListener = new ActionHandlesListener(
            ActionHandlesListener::BEHAVIOR_COMBINED,
            $this->layoutService->reset()
        );

        $eventManager = new EventManager();
        $listeners = $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH);
        $this->assertEquals(0, count($listeners));

        $handlesListener->attach($eventManager);

        $listeners = $eventManager->getListeners(\Zend\Mvc\MvcEvent::EVENT_DISPATCH);
        $this->assertEquals(1, count($listeners));

    }

    public function testFactory()
    {
        $factory = new ActionHandlesListenerFactory();
        $actionHandlesListener = $factory->createService($this->sm);
        $this->assertInstanceOf('ConLayout\Listener\ActionHandlesListener', $actionHandlesListener);
    }
}
