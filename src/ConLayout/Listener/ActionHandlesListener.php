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
     * Priority for the module handle.
     */
    const MODULE_HANDLE_PRIORITY = 10;
    
    /**
     * Priority for the controller handle.
     */
    const CONTROLLER_HANDLE_PRIORITY = 20;
    
    /**
     * Priority for the controller action handle.
     */
    const ACTION_HANDLE_PRIORITY = 30;
    
    /**
     * Priority for all error handles.
     */
    const ERROR_HANDLE_PRIORITY = 40;
    
    /**
     * Layout updater instance.
     *
     * @var LayoutUpdaterInterface
     */
    protected $updater;
    
    /**
     * Segments to exclude from the controller class name.
     *
     * @var array
     */
    protected $excludeSegments = [];
    
    /**
     * Inflector used to normalize names for use as action handles.
     *
     * @var CamelCaseToDash
     */
    protected $inflector;
    
    /**
     * Class constructor.
     *
     * @param LayoutUpdaterInterface $updater
     * @return ActionHandlesListener
     */
    public function __construct(LayoutUpdaterInterface $updater, array $excludeSegments = ['Controller'])
    {
        $this->updater = $updater;
        $this->excludeSegments = $excludeSegments;
    }
    
    /**
     * Attach event listeners for retrieving action handles.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'addActionHandles'], 1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'addErrorHandle'], 100);
    }
    
    /**
     * Callback handler invoked when the dispatch event is triggered.
     *
     * @param EventInterface $event
     * @return void
     */
    public function addActionHandles(EventInterface $event)
    {
        $handles = $this->getActionHandles($event);
        
        foreach ($handles as $handle)
        {
            $this->updater->addHandle($handle);
        }
    }
    
    /**
     * Callback handler invoked when the dispatch error event is triggered.
     *
     * @param EventInterface $event
     * @retrun void
     */
    public function addErrorHandle(EventInterface $event)
    {
        $this->updater->addHandle(new Handle($event->getError(), self::ERROR_HANDLE_PRIORITY));
    }
    
    /**
     * Retrieve the action handles from the matched route.
     *
     * @param EventInterface $event
     * @return array
     */
    private function getActionHandles(EventInterface $event)
    {
        /* @var $event \Zend\Mvc\MvcEvent */
        $routeMatch = $event->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $module = $this->deriveModuleNamespace($controller);
        $controller = $this->deriveControllerClass($controller);
        
        if ($namespace = $routeMatch->getParam(ModuleRouteListener::MODULE_NAMESPACE))
        {
            $controllerSubNs = $this->deriveControllerSubNamespace($namespace);
            
            if (!empty($controllerSubNs))
            {
                if (!empty($module))
                {
                    $module .= '-'.$controllerSubNs;
                }
                else
                {
                    $module = $controllerSubNs;
                }
            }
        }
        
        $module = $this->inflectName($module);
        $controller = $this->inflectName($controller);
        $action = $this->inflectName($action);
        
        if (empty($controller))
        {
            $controller = $action;
        }
        
        $actionHandles = [
            new Handle($module, self::MODULE_HANDLE_PRIORITY),
            new Handle($module.'-'.$controller, self::CONTROLLER_HANDLE_PRIORITY),
            new Handle($module.'-'.$controller.'-'.$action, self::ACTION_HANDLE_PRIORITY),
        ];
        
        return $actionHandles;
    }
    
    /**
     * Determine the top-level namespace of the controller.
     *
     * @param string $controller
     * @return string
     */
    private function deriveModuleNamespace($controller)
    {
        if (!strstr($controller, '\\'))
        {
            return '';
        }
        
        return substr($controller, 0, strpos($controller, '\\'));
    }
    
    /**
     * Determine the sub-level namespace of the controller.
     *
     * @param $namespace
     * @return string
     */
    private function deriveControllerSubNamespace($namespace)
    {
        if (!strstr($namespace, '\\'))
        {
            return '';
        }
        
        $nsArray = explode('\\', $namespace);
        $subNsArray = array_slice($nsArray, 2);
        
        if (empty($subNsArray))
        {
            return '';
        }
        
        return implode('-', $subNsArray);
    }
    
    /**
     * Determine the name of the controller and remove any excluded segments.
     *
     * @param string $controller
     * @return string
     */
    private function deriveControllerClass($controller)
    {
        if (strstr($controller, '\\'))
        {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }
        
        return str_replace($this->excludeSegments, '', $controller);
    }
    
    /**
     * Inflect a name to a normalized value.
     *
     * @param string $name
     * @return string
     */
    protected function inflectName($name)
    {
        if (!$this->inflector)
        {
            $this->inflector = new CamelCaseToDash();
        }
        
        return strtolower($this->inflector->filter($name));
    }
}