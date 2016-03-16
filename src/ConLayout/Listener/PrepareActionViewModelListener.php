<?php

namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;

/**
 * Listener to prepare action result view model
 *
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class PrepareActionViewModelListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * PrepareActionViewModelListener constructor.
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
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'prepareActionViewModel'], -300);
    }

    /**
     *
     * @param MvcEvent $e
     */
    public function prepareActionViewModel(MvcEvent $e)
    {
        /* @var $layout ModelInterface */
        $result = $e->getResult();
        if ($result instanceof ModelInterface && !$result->terminate()) {
            $this->layout->addBlock(
                LayoutInterface::BLOCK_ID_ACTION_RESULT,
                $result
            );
        }
    }
}
