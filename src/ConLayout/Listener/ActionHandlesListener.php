<?php
namespace ConLayout\Listener;

use ConLayout\Handle\Handle;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListener implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;
    
    /**
     * Character used to separate parts of a template name.
     */
    const TEMPLATE_SEPARATOR = '/';
    
    /**
     * Layout updater instance.
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;
    
    /**
     * Array of controller namespace -> action handle mappings.
     *
     * @var array
     */
    protected $controllerMap = [];
    
    /**
     * Flag to force the use of the route match controller param.
     *
     * @var boolean
     */
    protected $preferRouteMatchController = false;
    
    /**
     * Inflector used to normalize names for use as action handles.
     *
     * @var CamelCaseToDash
     */
    protected $inflector;
    
    /**
     * Retrieve the layout updater instance.
     *
     * @return LayoutUpdaterInterface
     */
    public function getUpdater()
    {
        return $this->updater;
    }
    
    /**
     * Set the layout updater instance.
     *
     * @param LayoutUpdaterInterface $updater
     * @return ActionHandlesListener
     */
    public function setUpdater(LayoutUpdaterInterface $updater)
    {
        $this->updater = $updater;
        
        return $this;
    }
    
    /**
     * Retrieve an array of controller namespace -> action handle mappings.
     *
     * @return array
     */
    public function getControllerMap()
    {
        return $this->controllerMap;
    }
    
    /**
     * Set an array of controller namespace -> action handle mappings.
     *
     * @param array $controllerMap
     * @return ActionHandlesListener
     */
    public function setControllerMap(array $controllerMap)
    {
        krsort($controllerMap);
        $this->controllerMap = $controllerMap;
        
        return $this;
    }
    
    /**
     * Whether to force the use of the route match controller param.
     *
     * @return boolean
     */
    public function isPreferRouteMatchController()
    {
        return $this->preferRouteMatchController;
    }
    
    /**
     * Set whether to force the use of the route match controller param.
     *
     * @param boolean $preferRouteMatchController
     * @return ActionHandlesListener
     */
    public function setPreferRouteMatchController($preferRouteMatchController)
    {
        $this->preferRouteMatchController = (boolean) $preferRouteMatchController;
        
        return $this;
    }
    
    /**
     * Attach event listeners for retrieving action handles.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'injectActionHandles'], 1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'injectErrorHandle'], 100);
    }
    
    /**
     * Callback handler invoked when the dispatch event is triggered.
     *
     * @param EventInterface $event
     * @return void
     */
    public function injectActionHandles(EventInterface $event)
    {
        $handles = $this->getActionHandles($event);
        
        foreach ($handles as $handle) {
            $this->updater->addHandle($handle);
        }
    }
    
    /**
     * Callback handler invoked when the dispatch error event is triggered.
     *
     * @param EventInterface $event
     * @return void
     */
    public function injectErrorHandle(EventInterface $event)
    {
        $this->updater->addHandle(new Handle($event->getError(), 666));
    }
    
    /**
     * Retrieve the action handles from the matched route.
     *
     * @param EventInterface $event
     * @return array
     */
    protected function getActionHandles(EventInterface $event)
    {
        /* @var $routeMatch MvcEvent */
        $routeMatch = $event->getRouteMatch();
        $controller = $event->getTarget();
        
        if (is_object($controller)) {
            $controller = get_class($controller);
        }
        
        $routeMatchController = $routeMatch->getParam('controller', '');
        if (!$controller || ($this->preferRouteMatchController && $routeMatchController)) {
            $controller = $routeMatchController;
        }
        
        $template = $this->mapController($controller);
        if (!$template) {
            $module     = $this->deriveModuleNamespace($controller);
            
            if ($namespace = $routeMatch->getParam(ModuleRouteListener::MODULE_NAMESPACE)) {
                $controllerSubNs = $this->deriveControllerSubNamespace($namespace);
                if (!empty($controllerSubNs)) {
                    if (!empty($module)) {
                        $module .= self::TEMPLATE_SEPARATOR . $controllerSubNs;
                    } else {
                        $module = $controllerSubNs;
                    }
                }
            }
            
            $controller = $this->deriveControllerClass($controller);
            $template = $this->inflectName($module);
            
            if (!empty($template)) {
                $template .= self::TEMPLATE_SEPARATOR;
            }
            $template .= $this->inflectName($controller);
        }
        
        $action = $routeMatch->getParam('action');
        if (null !== $action) {
            $template .= self::TEMPLATE_SEPARATOR . $this->inflectName($action);
        }
        
        $priority = 0;
        $actionHandles = [];
        $previousHandle = '';
        $templateParts = explode(self::TEMPLATE_SEPARATOR, $template);
        
        foreach ($templateParts as $index => $name) {
            $priority += 10;
            $actionHandles[] = new Handle($previousHandle.$name, $priority);
            $previousHandle .= $name.self::TEMPLATE_SEPARATOR;
        }
        
        return $actionHandles;
    }
    
    /**
     * Map a controller to action handle if a controller namespace is white-listed or mapped.
     *
     * @param string $controller
     * @return string|false
     */
    public function mapController($controller)
    {
        if (!is_string($controller)) {
            return false;
        }
        
        foreach ($this->controllerMap as $namespace => $replacement) {
            if (false == $replacement
                || !($controller === $namespace || strpos($controller, $namespace . '\\') === 0)
            ) {
                continue;
            }
            
            $map = '';
            
            if (is_string($replacement)) {
                $map = rtrim($replacement, self::TEMPLATE_SEPARATOR) . self::TEMPLATE_SEPARATOR;
                $controller = substr($controller, strlen($namespace) + 1) ?: '';
            }
            
            $parts = explode('\\', $controller);
            array_pop($parts);
            $parts = array_diff($parts, array('Controller'));
            $parts[] = $this->deriveControllerClass($controller);
            $controller = implode(self::TEMPLATE_SEPARATOR, $parts);
            $template = trim($map . $controller, self::TEMPLATE_SEPARATOR);
            
            return $this->inflectName($template);
        }
        return false;
    }
    
    /**
     * Inflect a name to a normalized value.
     *
     * @param string $name
     * @return string
     */
    private function inflectName($name)
    {
        if (!$this->inflector) {
            $this->inflector = new CamelCaseToDash();
        }
        $name = $this->inflector->filter($name);
        return strtolower($name);
    }
    
    /**
     * Determine the top-level namespace of the controller.
     *
     * @param string $controller
     * @return string
     */
    private function deriveModuleNamespace($controller)
    {
        if (!strstr($controller, '\\')) {
            return '';
        }
        $module = substr($controller, 0, strpos($controller, '\\'));
        return $module;
    }
    
    /**
     * Determine the sub-namespace of the controller.
     *
     * @param string $namespace
     * @return string
     */
    private function deriveControllerSubNamespace($namespace)
    {
        if (!strstr($namespace, '\\')) {
            return '';
        }
        
        $nsArray = explode('\\', $namespace);
        $subNsArray = array_slice($nsArray, 2);
        
        if (empty($subNsArray)) {
            return '';
        }
        return implode(self::TEMPLATE_SEPARATOR, $subNsArray);
    }
    
    /**
     * Determine the name of the controller, stripping the namespace, and the suffix "Controller" if present.
     *
     * @param string $controller
     * @return string
     */
    private function deriveControllerClass($controller)
    {
        if (strstr($controller, '\\')) {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }
        
        if ((10 < strlen($controller))
            && ('Controller' == substr($controller, -10))
        ) {
            $controller = substr($controller, 0, -10);
        }
        
        return $controller;
    }
}
