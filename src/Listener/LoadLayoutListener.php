<?php

declare(strict_types=1);

namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ModelInterface;

class LoadLayoutListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    protected \ConLayout\Layout\LayoutInterface $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     *
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'loadLayout'], $priority);
    }

    /**
     * load layout if result ist not terminated
     */
    public function loadLayout(MvcEvent $e): void
    {
        /* @var $result ModelInterface */
        $result = $e->getViewModel();
        if (!$result->terminate()) {
            $this->layout->load();
        }
    }
}
