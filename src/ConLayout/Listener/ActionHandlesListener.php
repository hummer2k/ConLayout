<?php
namespace ConLayout\Listener;

use ConLayout\Handle\Controller;
use ConLayout\Handle\ControllerAction;
use ConLayout\Handle\Route;
use ConLayout\Service\LayoutService;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ViewModel;
    
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListener
    implements  ListenerAggregateInterface,
                ServiceLocatorAwareInterface    
{
    use ListenerAggregateTrait;    
    use ServiceLocatorAwareTrait;

    protected $updater;
                            
    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH,  array($this, 'addActionHandles'), 999);
    }
    
    /**
     * 
     * @param EventInterface $event
     * @return ActionHandlesListener
     */
    public function addActionHandles(EventInterface $event)
    {
        $routeMatch = $event->getRouteMatch();
        $handles = $this->getActionHandles($routeMatch);
        foreach ($handles as $handle) {
            $this->updater->addHandle($handle);
        }
        return $this;
    }
    
    /**
     * 
     * @param RouteMatch $routeMatch
     * @return array
     */
    public function getActionHandles(RouteMatch $routeMatch)
    {
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $actionHandles = array();
        $namespaceSegments = explode('\\', $controller);
        $count = count($namespaceSegments);
        for ($i = 1; $i <= $count; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $actionHandles[$i][] = $namespaceSegments[$j];
            }
            $actionHandles[$i] = new Handle(strtolower(implode('-', $actionHandles[$i])), $i);
        }
        $actionHandles[] = new Handle(strtolower(implode('-', $actionHandles[$i])) . '-' . $action, $i + 1);
        return $actionHandles;
    }
}
