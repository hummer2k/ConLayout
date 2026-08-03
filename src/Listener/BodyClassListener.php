<?php

namespace ConLayout\Listener;

use ConLayout\View\Helper\BodyClass;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListener extends AbstractListenerAggregate
{
    /**
     *
     * @var BodyClass
     */
    protected $bodyClassHelper;

    /**
     * @var string
     */
    private $prefix;

    /**
     *
     * @param BodyClass $bodyClassHelper
     * @param string $prefix
     */
    public function __construct(BodyClass $bodyClassHelper, string $prefix = '')
    {
        $this->bodyClassHelper = $bodyClassHelper;
        $this->prefix = $prefix;
    }

    /**
     *
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'addBodyClass'], $priority);
    }

    /**
     *
     * @param MvcEvent $e
     * @return BodyClassListener
     */
    public function addBodyClass(MvcEvent $e)
    {
        $helper = $this->bodyClassHelper;
        $routeMatchName = $e->getRouteMatch()->getMatchedRouteName();
        $className = $this->prefix . preg_replace('#[^a-z0-9-]+#i', '-', $routeMatchName);
        $helper(strtolower($className));
        return $this;
    }
}
