<?php
namespace ConLayoutTest\Listener;

use ConLayout\Listener\ActionHandles;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesTest extends \ConLayoutTest\AbstractTest
{
    /**
     * @covers ActionHandles::getActionHandles
     * @covers ActionHandles::getControllerHandles
     */
    public function testControllerHandles()
    {
        $routeMatch = new \Zend\Mvc\Router\Http\RouteMatch(array(
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ));
        $handlesListener = new ActionHandles(
            ActionHandles::BEHAVIOR_CONTROLLER,
            $this->layoutConfig->reset()
        );
        
        $this->assertSame(
            $handlesListener->getActionHandles($routeMatch),
            array(
                'application',
                'application\controller\index',
                'application\controller\index::index'
            )
        );
    }
    
    /**
     * @covers ActionHandles::getActionHandles
     * @covers ActionHandles::getRouteHandles
     */
    public function testRouteHandles()
    {
        $routeMatch = new \Zend\Mvc\Router\Http\RouteMatch(array(
            'controller' => 'Application\Controller\Index',
            'action' => 'index'
        ));
        $routeMatch->setMatchedRouteName('user/login');
        $handlesListener = new ActionHandles(
            ActionHandles::BEHAVIOR_ROUTENAME,
            $this->layoutConfig->reset()
        );
        
        $this->assertSame(
            $handlesListener->getActionHandles($routeMatch),
            array(
                'user',
                'user/login'
            )
        );
    }
    
    /**
     * @covers ActionHandles::getActionHandles
     * @covers ActionHandles::getRouteHandles
     * @covers ActionHandles::getControllerHandles
     */
    public function testCombinedHandles()
    {
        $routeMatch = new \Zend\Mvc\Router\Http\RouteMatch(array(
            'controller' => 'User\Controller\Index',
            'action' => 'login'
        ));
        $routeMatch->setMatchedRouteName('user/login');
        $handlesListener = new ActionHandles(
            ActionHandles::BEHAVIOR_COMBINED,
            $this->layoutConfig->reset()
        );
        
        $this->assertSame(
            array_values($handlesListener->getActionHandles($routeMatch)),
            array(
                'user',
                'user/login',
                'user\controller\index',
                'user\controller\index::login'
            )
        );
       
    }
}
