<?php

declare(strict_types=1);

namespace ConLayout\Updater;

use ConLayout\Handle\Handle;
use ConLayout\Handle\HandleInterface;
use ConLayout\Updater\Collector\CollectorInterface;
use ConLayout\Updater\Event\UpdateEvent;
use Laminas\Config\Config;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Stdlib\PriorityList;

final class LayoutUpdater implements
    EventManagerAwareInterface,
    LayoutUpdaterInterface
{
    use EventManagerAwareTrait;

    /**
     *
     * @var Config|null
     */
    private ?Config $layoutStructure = null;

    /**
     * @var PriorityList|CollectorInterface[]
     */
    private PriorityList $collectors;

    /**
     * Format:
     * (string) handle-name => (int) priority
     */
    protected array $handles = [];

    /**
     * @var array|HandleInterface[]
     */
    protected array $oHandles = [];

    protected string $area = self::AREA_DEFAULT;

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
    public function addHandle(HandleInterface $handle): static
    {
        $this->handles[$handle->getName()] = $handle->getPriority();
        $this->oHandles[$handle->getName()] = $handle;
        return $this;
    }

    /**
     *
     * @param array|HandleInterface[] $handles
     */
    public function setHandles(array $handles): static
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
    public function removeHandle(string $handleName): static
    {
        if (isset($this->handles[$handleName])) {
            unset($this->handles[$handleName]);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandles(bool $asObject = false): array
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
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     *
     * {@inheritDoc}
     */
    public function setArea(string $area): static
    {
        $this->area = $area;
        return $this;
    }

    public function attachCollector(string $name, CollectorInterface $collector, int $priority = 1): static
    {
        $this->collectors->insert($name, $collector, $priority);
        return $this;
    }

    public function detachCollector(string $name): static
    {
        $this->collectors->remove($name);
        return $this;
    }

    public function getLayoutStructure(): Config
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
                $event,
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

    private function fetchUpdates(): void
    {
        $handles = $this->getHandles();
        foreach ($handles as $handle) {
            $this->fetchHandle($handle);
        }
    }

    private function fetchHandle(string $handle): void
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
