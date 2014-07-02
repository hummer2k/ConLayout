<?php
namespace ConLayout\Listener;

use Zend\EventManager\ListenerAggregateInterface,
    Zend\EventManager\EventManagerInterface,
    Zend\EventManager\EventInterface,
    Zend\Mvc\MvcEvent,
    Zend\EventManager\ListenerAggregateTrait,
    ConLayout\Service\Config,
    Zend\Mvc\Router\Http\RouteMatch;
    
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandles 
    implements  ListenerAggregateInterface
{
    use ListenerAggregateTrait;    
            
    /**
     *
     * @var string
     */
    protected $handleBehavior;
    
    /**
     *
     * @var \ConLayout\Service\Config
     */
    protected $layoutConfig;
    
    /**
     * 
     * @param string $handleBehavior
     */
    public function __construct($handleBehavior, Config $layoutConfig)
    {
        $this->setHandleBehavior($handleBehavior);
        $this->setLayoutConfig($layoutConfig);
    }
    
    /**
     * 
     * @param \Zend\EventManager\EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'addActionHandles'));
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
        $this->layoutConfig->addHandle($handles);
        return $this;
    }
    
    /**
     * 
     * @param \Zend\EventManager\EventInterface $event
     * @return array
     */
    protected function getActionHandles(RouteMatch $routeMatch)
    {
        $routeName = $routeMatch->getMatchedRouteName();
        $controller = strtolower($routeMatch->getParam('controller'));
        $action = strtolower($routeMatch->getParam('action'));
        $module = substr($controller, 0, strpos($controller, '\\'));
        
        $controllerAction = array(
            $module,
            $controller,
            $controller . '::' . $action
        );
        
        switch ($this->getHandleBehavior()) {
        case 'routematch':
            return array($routeName);
        case 'combined':
            array_unshift($controllerAction, $routeName);
            return $controllerAction;       
        case 'controller_action':
        default:
            return $controllerAction;
        }
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
     * @return Config
     */
    public function getLayoutConfig()
    {
        return $this->layoutConfig;
    }

    /**
     * 
     * @param \ConLayout\Service\Config $layoutConfig
     * @return \ConLayout\Listener\ActionHandles
     */
    public function setLayoutConfig(Config $layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;
        return $this;
    }
    
}
