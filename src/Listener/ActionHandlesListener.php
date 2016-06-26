<?php
namespace ConLayout\Listener;

use ConLayout\Handle\Handle;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\InjectTemplateListener;
use Zend\Router\Http\RouteMatch;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ActionHandlesListener extends InjectTemplateListener
{
    const SEPARATOR = '/';

    /**
     * Layout updater instance.
     *
     * @var LayoutUpdaterInterface
     */
    private $updater;

    /**
     * ActionHandlesListener constructor.
     * @param LayoutUpdaterInterface $updater
     */
    public function __construct(LayoutUpdaterInterface $updater)
    {
        $this->updater = $updater;
    }

    /**
     * Attach event listeners for retrieving action handles.
     *
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1000)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'injectActionHandles'], $priority);
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
    private function getActionHandles(EventInterface $event)
    {
        /** @var RouteMatch $routeMatch */
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

        $action = $routeMatch->getParam('action');
        if (null !== $action) {
            $template .= self::SEPARATOR . $this->inflectName($action);
        }

        $priority = 0;
        $actionHandles = [];
        $previousHandle = '';
        $templateParts = explode(self::SEPARATOR, $template);

        foreach ($templateParts as $name) {
            $priority += 10;
            $actionHandles[] = new Handle($previousHandle.$name, $priority);
            $previousHandle .= $name.self::SEPARATOR;
        }

        return $actionHandles;
    }
}
