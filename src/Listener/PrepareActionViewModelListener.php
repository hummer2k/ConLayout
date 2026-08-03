<?php

declare(strict_types=1);

namespace ConLayout\Listener;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Layout\LayoutInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ModelInterface;

/**
 * Listener to prepare action result view model
 *
 */
class PrepareActionViewModelListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var LayoutInterface
     */
    private \ConLayout\Block\BlockPoolInterface $blockPool;

    /**
     * PrepareActionViewModelListener constructor.
     */
    public function __construct(BlockPoolInterface $blockPool)
    {
        $this->blockPool = $blockPool;
    }

    /**
     *
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = -300): void
    {
        foreach ([MvcEvent::EVENT_DISPATCH, MvcEvent::EVENT_DISPATCH_ERROR] as $eventName) {
            $this->listeners[] = $events->attach(
                $eventName,
                [$this, 'prepareActionViewModel'],
                $priority
            );
        }
    }

    public function prepareActionViewModel(MvcEvent $e): void
    {
        /* @var $result ModelInterface */
        $result = $e->getResult();
        if ($result instanceof ModelInterface) {
            if ($result->terminate()) {
                $result->setOption('has_parent', null);
            } else {
                $this->blockPool->add(
                    LayoutInterface::BLOCK_ID_ACTION_RESULT,
                    $result
                );
            }
        }
    }
}
