<?php

declare(strict_types=1);

namespace ConLayout\Listener;

use ConLayout\View\Helper\BodyClass;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;

class BodyClassListener extends AbstractListenerAggregate
{
    protected \ConLayout\View\Helper\BodyClass $bodyClassHelper;

    private string $prefix;

    public function __construct(BodyClass $bodyClassHelper, string $prefix = '')
    {
        $this->bodyClassHelper = $bodyClassHelper;
        $this->prefix = $prefix;
    }

    /**
     *
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'addBodyClass'], $priority);
    }

    public function addBodyClass(MvcEvent $e): static
    {
        $helper = $this->bodyClassHelper;
        $routeMatchName = $e->getRouteMatch()->getMatchedRouteName();
        $className = $this->prefix . preg_replace('#[^a-z0-9-]+#i', '-', $routeMatchName);
        $helper(strtolower($className));
        return $this;
    }
}
