<?php
namespace ConLayout\Listener;

use ConLayout\Service\LayoutService,
    Zend\EventManager\EventInterface,
    Zend\EventManager\EventManagerInterface,
    Zend\EventManager\ListenerAggregateInterface,
    Zend\EventManager\ListenerAggregateTrait,
    Zend\Mvc\MvcEvent,
    Zend\Mvc\Router\Http\RouteMatch,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorAwareTrait,
    Zend\View\Model\ViewModel;
    
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
    
    const BEHAVIOR_CONTROLLER = 'controller'; 
    const BEHAVIOR_ROUTENAME = 'routename';    
    const BEHAVIOR_COMBINED = 'combined';
            
    /**
     *
     * @var string
     */
    protected $handleBehavior;
    
    /**
     *
     * @var LayoutService
     */
    protected $layoutService;
    
    /**
     *
     * @var string
     */
    protected $routeSeparator = '/';
    
    /**
     *
     * @var ViewModel
     */
    protected $contentViewModel;
    
    /**
     * 
     * @param string $handleBehavior
     */
    public function __construct(
        $handleBehavior, 
        LayoutService $layoutService
    )
    {
        $this->setHandleBehavior($handleBehavior);
        $this->setLayoutService($layoutService);
    }
    
    /**
     * 
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE,  array($this, 'addActionHandles'));
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
        $this->layoutService->addHandle($handles);
        return $this;
    }
    
    /**
     * 
     * @param RouteMatch $routeMatch
     * @return array
     */
    public function getActionHandles(RouteMatch $routeMatch)
    {
        $routeHandles = $this->getRouteHandles($routeMatch->getMatchedRouteName());
        $controllerHandles = $this->getControllerHandles($routeMatch);

        switch ($this->getHandleBehavior()) {
            case self::BEHAVIOR_ROUTENAME:
                return $routeHandles;
            case self::BEHAVIOR_COMBINED:
                return array_unique(array_merge($routeHandles, $controllerHandles));    
            case self::BEHAVIOR_CONTROLLER:
            default:
                return $controllerHandles;
        }
    }
    
    /**
     * 
     * @param RouteMatch $routeMatch
     * @return array
     */
    protected function getControllerHandles(RouteMatch $routeMatch)
    {
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $module = substr($controller, 0, strpos($controller, '\\'));
        return array(
            $module,
            $controller,
            $controller . '::' . $action
        );
    }
    
    /**
     * 
     * @param string $routeName
     * @return array
     */
    protected function getRouteHandles($routeName)
    {
        $routeSegments = explode($this->routeSeparator, $routeName);
        $routeHandles = array();
        $count = count($routeSegments);
        for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $routeHandles[$i][] = $routeSegments[$j];
            }
            $routeHandles[$i] = implode($this->routeSeparator, $routeHandles[$i]);
        }
        return $routeHandles;
    }
    
    /**
     * 
     * @return string
     */
    public function getHandleBehavior()
    {
        return $this->handleBehavior;
    }
    
    /**
     * 
     * @param string $handleBehavior
     * @return ActionHandlesListener
     */
    public function setHandleBehavior($handleBehavior)
    {
        $this->handleBehavior = $handleBehavior;
        return $this;
    }
    
    /**
     * 
     * @return LayoutService
     */
    public function getLayoutService()
    {
        return $this->layoutService;
    }

    /**
     * 
     * @param LayoutService $layoutService
     * @return ActionHandlesListener
     */
    public function setLayoutService(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRouteSeparator()
    {
        return $this->routeSeparator;
    }

    /**
     * 
     * @param string $routeSeparator
     * @return ActionHandlesListener
     */
    public function setRouteSeparator($routeSeparator)
    {
        $this->routeSeparator = $routeSeparator;
        return $this;
    }
}
