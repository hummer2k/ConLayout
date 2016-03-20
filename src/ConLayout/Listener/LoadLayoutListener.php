<?php
namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;

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
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'loadLayout']);
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
