<?php
namespace ConLayout\Listener;

use ConLayout\Handle\Handle;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\InjectTemplateListener;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListener extends InjectTemplateListener
{
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

        foreach ($templateParts as $name) {
            $priority += 10;
            $actionHandles[] = new Handle($previousHandle.$name, $priority);
            $previousHandle .= $name.self::TEMPLATE_SEPARATOR;
        }

        return $actionHandles;
    }
}
