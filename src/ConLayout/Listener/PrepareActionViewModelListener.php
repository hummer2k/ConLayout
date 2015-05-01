<?php

namespace ConLayout\Listener;

use ConLayout\Layout\LayoutInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;

/**
 * Listener to set action results' block name
 *
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class PrepareActionViewModelListener implements
    ListenerAggregateInterface
{
    use ListenerAggregateTrait;

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
        if ($result instanceof ModelInterface) {
            $result->setVariable(
                LayoutInterface::BLOCK_ID_VAR,
                LayoutInterface::BLOCK_ID_ACTION_RESULT
            );
            $result->setAppend(true);
        }
    }
}
