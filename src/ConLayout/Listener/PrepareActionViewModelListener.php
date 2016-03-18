<?php

namespace ConLayout\Listener;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Layout\LayoutInterface;
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
    private $blockPool;

    /**
     * PrepareActionViewModelListener constructor.
     * @param BlockPoolInterface $blockPool
     */
    public function __construct(BlockPoolInterface $blockPool)
    {
        $this->blockPool = $blockPool;
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
        /* @var $result ModelInterface */
        $result = $e->getResult();
        if ($result instanceof ModelInterface && !$result->terminate()) {
            $this->blockPool->add(
                LayoutInterface::BLOCK_ID_ACTION_RESULT,
                $result
            );
        }
    }
}
