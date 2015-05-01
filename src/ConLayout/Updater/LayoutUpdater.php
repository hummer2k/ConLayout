<?php

namespace ConLayout\Updater;

use ConLayout\Updater\Event\FetchEvent;
use ConLayout\Updater\Event\UpdateEvent;
use Zend\Config\Config;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
final class LayoutUpdater extends AbstractUpdater implements
    EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /**
     *
     * @var Config
     */
    private $layoutStructure;

    /**
     * @var FetchEvent
     */
    private $event;

    /**
     *
     * @return Config
     */
    public function getLayoutStructure()
    {
        if (null === $this->layoutStructure) {
            $this->layoutStructure = new Config([], true);

            $handles = $this->getHandles();
            $event = new UpdateEvent();
            $event->setLayoutStructure($this->layoutStructure);
            $event->setHandles($handles);

            $results = $this->getEventManager()->trigger(
                __FUNCTION__ . '.pre',
                $this,
                $event,
                function ($result) {
                    return ($result instanceof Config);
                }
            );

            if ($results->stopped()) {
                $this->layoutStructure = $results->last();
            } else {
                foreach ($handles as $handle) {
                    $this->fetch($handle);
                }
            }

            $this->getEventManager()->trigger(
                __FUNCTION__ . '.post',
                $this,
                ['__RESULT__' => $this->layoutStructure]
            );

            $this->layoutStructure->setReadOnly();
        }
        return $this->layoutStructure;
    }

    /**
     *
     * @param string $handle
     */
    private function fetch($handle)
    {
        $event = $this->getEvent();
        $event->setHandle($handle);
        $this->events->trigger(
            __FUNCTION__,
            $this,
            $event
        );
    }

    /**
     *
     * @return FetchEvent
     */
    private function getEvent()
    {
        if (null === $this->event) {
            $this->event = new FetchEvent();
            $this->event->setLayoutStructure($this->layoutStructure);
        }
        return $this->event;
    }
}
