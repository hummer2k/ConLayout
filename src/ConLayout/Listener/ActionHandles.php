<?php
namespace ConLayout\Listener;

use Zend\EventManager\ListenerAggregateInterface,
    Zend\EventManager\EventManagerInterface,
    Zend\EventManager\EventInterface,
    Zend\Mvc\MvcEvent,
    Zend\EventManager\ListenerAggregateTrait,
    ConLayout\Service\LayoutService,
    Zend\Mvc\Router\Http\RouteMatch,
    Zend\ServiceManager\ServiceLocatorAwareTrait;
    
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandles 
    implements  ListenerAggregateInterface,
                \Zend\ServiceManager\ServiceLocatorAwareInterface    
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
     * @var \ConLayout\Service\LayoutService
     */
    protected $layoutService;
    
    /**
     *
     * @var string
     */
    protected $routeSeparator = '/';
    
    /**
     * 
     * @param string $handleBehavior
     */
    public function __construct($handleBehavior, LayoutService $layoutService)
    {
        $this->setHandleBehavior($handleBehavior);
        $this->setLayoutService($layoutService);
    }
    
    /**
     * 
     * @param \Zend\EventManager\EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'addActionHandles'));
        $this->listeners[] = $events->attach([MvcEvent::EVENT_ROUTE, MvcEvent::EVENT_DISPATCH_ERROR], array($this, 'setLayoutTemplate'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'prepareView'));
    }
    
    /**
     * prepares view: applies callbacks 
     * 
     * @deprecated since version 0.1
     * @param \Zend\EventManager\EventInterface $event
     * @return \ConLayout\Listener\ActionHandles
     */
    public function prepareView(EventInterface $event)
    {
        $layoutConfig = $this->getLayoutService()->getLayoutConfig();
        $prepareView = $layoutConfig->prepareView;
        if (!is_array($prepareView)) {
            $prepareView = array($prepareView);
        }
        foreach ($prepareView as $callback) {
            if (is_callable($callback)) {
                $callback($this->getViewRenderer());
            }
        }
        return $this;
    }
    
    /**
     * retrieve ViewRenderer
     * 
     * @return \Zend\View\Renderer\PhpRenderer
     */
    public function getViewRenderer()
    {
        return $this->serviceLocator->get('ViewRenderer');
    }
    
    public function setLayoutTemplate(EventInterface $event)
    {
        /* @var $layout \Zend\View\Model\ViewModel */
        $layout = $event->getViewModel();
        $layout->setTemplate($this->getLayoutService()->getLayoutTemplate());
        return $this;
    }
    
    /**
     * 
     * @param \Zend\EventManager\EventInterface $event
     * @return \ConLayout\Listener\ActionHandles
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
     * @param \Zend\EventManager\EventInterface $event
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
     * @param \Zend\Mvc\Router\Http\RouteMatch $routeMatch
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
     * @return \ConLayout\Listener\ActionHandles
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
     * @param \ConLayout\Service\LayoutService $layoutService
     * @return \ConLayout\Listener\ActionHandles
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
     * @return \ConLayout\Listener\ActionHandles
     */
    public function setRouteSeparator($routeSeparator)
    {
        $this->routeSeparator = $routeSeparator;
        return $this;
    }
    
}
