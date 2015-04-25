<?php
namespace ConLayout\Listener;

use ConLayout\Handle\Handle;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
    
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListener implements
    ListenerAggregateInterface,
    ServiceLocatorAwareInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    /**
     *
     * @param LayoutUpdaterInterface $updater
     */
    public function __construct(LayoutUpdaterInterface $updater)
    {
        $this->updater = $updater;
    }

    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'addActionHandles'), 999);
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
    private function getActionHandles(RouteMatch $routeMatch)
    {
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $actionHandles = array();
        $namespaceSegments = explode('\\', $controller);
        $count = count($namespaceSegments);
        for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $actionHandles[$i][] = $namespaceSegments[$j];
            }
            $handleName = strtolower(implode('-', $actionHandles[$i]));
            $actionHandles[$i] = new Handle($handleName, $j);
        }
        $actionHandles[] = new Handle($handleName . '-' . $action, $j + 1);
        return $actionHandles;
    }
}
