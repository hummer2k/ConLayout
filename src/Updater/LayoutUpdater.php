<?php

namespace ConLayout\Updater;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\Updater\Collector\CollectorInterface;
use ConLayout\Updater\Event\UpdateEvent;
use Laminas\Config\Config;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\PriorityList;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
final class LayoutUpdater implements
    EventManagerAwareInterface,
    LayoutUpdaterInterface
{
    use EventManagerAwareTrait;

    /**
     *
     * @var Config
     */
    private $layoutStructure;

    /**
     * @var PriorityList|CollectorInterface[]
     */
    private $collectors;

    /**
     * Format:
     * (string) handle-name => (int) priority
     *
     * @var array
     */
    protected $handles = [];

    /**
     * @var array|HandleInterface[]
     */
    protected $oHandles = [];

    /**
     *
     * @var string
     */
    protected $area = self::AREA_DEFAULT;

    /**
     * AbstractUpdater constructor.
     */
    public function __construct()
    {
        $this->addHandle(new Handle('default', -1));
        $this->collectors = new PriorityList();
    }

    /**
     * {@inheritdoc}
     */
    public function addHandle(HandleInterface $handle)
    {
        $this->handles[$handle->getName()] = $handle->getPriority();
        $this->oHandles[$handle->getName()] = $handle;
        return $this;
    }

    /**
     *
     * @param array|HandleInterface[] $handles
     * @return AbstractUpdater
     */
    public function setHandles(array $handles)
    {
        $this->handles = [];
        foreach ($handles as $handle) {
            $this->addHandle($handle);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeHandle($handleName)
    {
        if (isset($this->handles[$handleName])) {
            unset($this->handles[$handleName]);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandles($asObject = false)
    {
        asort($this->handles);
        if ($asObject) {
            $handles = [];
            foreach (array_keys($this->handles) as $handle) {
                $handles[] = $this->oHandles[$handle];
            }
            return $handles;
        }
        return array_keys($this->handles);
    }

    /**
     * {@inheritDoc}
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     *
     * {@inheritDoc}
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }

    /**
     * @param string $name
     * @param CollectorInterface $collector
     * @param int $priority
     * @return $this
     */
    public function attachCollector($name, CollectorInterface $collector, $priority = 1)
    {
        $this->collectors->insert($name, $collector, $priority);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function detachCollector($name)
    {
        $this->collectors->remove($name);
        return $this;
    }

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
            $event->setName(UpdateEvent::EVENT_COLLECT);
            $event->setTarget($this);

            $results = $this->getEventManager()->triggerEventUntil(
                function ($result) {
                    return ($result instanceof Config);
                },
                $event
            );

            if ($results->stopped()) {
                $this->layoutStructure = $results->last();
            } else {
                $this->fetchUpdates();
                $event->setName(UpdateEvent::EVENT_COLLECT_POST);
                $this->getEventManager()->triggerEvent($event);
            }

            $this->layoutStructure->setReadOnly();
        }
        return $this->layoutStructure;
    }

    private function fetchUpdates()
    {
        $handles = $this->getHandles();
        foreach ($handles as $handle) {
            $this->fetchHandle($handle);
        }
    }

    /**
     * @param string $handle
     */
    private function fetchHandle($handle)
    {
        foreach ($this->collectors->getIterator() as $collector) {
            $tempStructure = $collector->collect($handle, $this->getArea());
            if ($includes = $tempStructure->get(self::INSTRUCTION_INCLUDE)) {
                foreach ($includes as $include) {
                    $this->fetchHandle($include);
                }
            }
            $this->layoutStructure->merge($tempStructure);
        }
    }
}
