<?php

namespace ConLayout\Updater;

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
            $event->setArea($this->getArea());

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
                $this->getEventManager()->trigger(
                    __FUNCTION__ . '.post',
                    $this,
                    $event
                );
            }

            $this->layoutStructure->setReadOnly();
        }
        return $this->layoutStructure;
    }
}
