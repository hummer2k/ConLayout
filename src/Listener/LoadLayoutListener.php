<?php

namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ModelInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LoadLayoutListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     *
     * @var LayoutInterface
     */
    protected $layout;

    /**
     *
     * @param LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     *
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'loadLayout'], $priority);
    }

    /**
     * load layout if result ist not terminated
     *
     * @param MvcEvent $e
     */
    public function loadLayout(MvcEvent $e)
    {
        /* @var $result ModelInterface */
        $result = $e->getViewModel();
        if (!$result->terminate()) {
            $this->layout->load();
        }
    }
}
