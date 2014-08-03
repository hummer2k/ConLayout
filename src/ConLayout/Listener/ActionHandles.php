<?php
namespace ConLayout\Listener;

use Zend\EventManager\ListenerAggregateInterface,
    Zend\EventManager\EventManagerInterface,
    Zend\EventManager\EventInterface,
    Zend\Mvc\MvcEvent,
    Zend\EventManager\ListenerAggregateTrait,
    ConLayout\Service\LayoutService,
    Zend\Mvc\Router\Http\RouteMatch,
    Zend\ServiceManager\ServiceLocatorAwareTrait,
    ConLayout\ValuePreparer\ValuePreparerInterface;
    
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
     * @var array
     */
    protected $helperConfig = array();
    
    /**
     * value preparers for view helpers in format:
     *   'helperName' => array(ConLayout\ValuePreparer\ValuePreparerInterface)
     *  
     * @var array
     */
    protected $valuePreparers = array();
    
    /**
     * 
     * @param string $handleBehavior
     */
    public function __construct(
        $handleBehavior, 
        LayoutService $layoutService, 
        array $helperConfig = array()
    )
    {
        $this->setHandleBehavior($handleBehavior);
        $this->setLayoutService($layoutService);
        $this->setHelperConfig($helperConfig);
    }
    
    /**
     * 
     * @param \Zend\EventManager\EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'addActionHandles'));
        $this->listeners[] = $events->attach([MvcEvent::EVENT_ROUTE, MvcEvent::EVENT_DISPATCH_ERROR], array($this, 'setLayoutTemplate'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'applyHelpers'));
    }
    
    /**
     * applies view helpers 
     *
     * @param \Zend\EventManager\EventInterface $event
     * @return \ConLayout\Listener\ActionHandles
     */
    public function applyHelpers(EventInterface $event)
    {        
        $layoutConfig = $this->getLayoutService()
            ->getLayoutConfig();
        foreach ($this->getHelperConfig() as $helper => $config) {
            if (!isset($layoutConfig[$helper])) continue;
            $defaultMethod = isset($config['defaultMethod']) ? $config['defaultMethod'] : '__invoke';
            $viewHelper = $this->getViewRenderer()->plugin($helper);
            if (!is_array($layoutConfig[$helper])) {
                $layoutConfig[$helper] = array($layoutConfig[$helper]);
            }
            foreach ($layoutConfig[$helper] as $value) {
                if (is_array($value)) {
                    $method = isset($value['method']) ? $value['method'] : $defaultMethod;
                    $args   = isset($value['args']) ? $value['args'] : $value;
                    $args[0] = $this->prepareHelperValue($args[0], $helper);
                    call_user_func_array(array($viewHelper, $method), $args);
                } else if (is_string($value)) {
                    $viewHelper->{$defaultMethod}($this->prepareHelperValue($value, $helper));
                }
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param mixed $value value to prepare
     * @param string $helper view helper name
     * @return mixed
     */
    private function prepareHelperValue($value, $helper)
    {
        if (!isset($this->valuePreparers[$helper])) {
            return $value;
        }
        /* @var $valuePreparer ValuePreparerInterface */
        foreach ($this->valuePreparers[$helper] as $valuePreparer) {
            $value = $valuePreparer->prepare($value);
        }
        return $value;
    }
    
    /**
     * 
     * @param string $helper view helper
     * @param \ConLayout\ValuePreparer\ValuePreparerInterface $valuePreparer
     * @return \ConLayout\Listener\ActionHandles
     */
    public function addValuePreparer($helper, ValuePreparerInterface $valuePreparer)
    {
        $this->valuePreparers[$helper][] = $valuePreparer;
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
    
    /**
     * retrieve helper config
     * 
     * @return array
     */
    public function getHelperConfig()
    {
        return $this->helperConfig;
    }

    /**
     * 
     * @param array $helperConfig
     * @return \ConLayout\Listener\ActionHandles
     */
    public function setHelperConfig(array $helperConfig)
    {
        $this->helperConfig = $helperConfig;
        return $this;
    }


}
