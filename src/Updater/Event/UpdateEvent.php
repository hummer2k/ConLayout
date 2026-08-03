<?php

declare(strict_types=1);

namespace ConLayout\Updater\Event;

use Laminas\Config\Config;
use Laminas\EventManager\Event;

class UpdateEvent extends Event
{
    public const EVENT_COLLECT      = 'collect';
    public const EVENT_COLLECT_POST = 'collect.post';

    protected Config $layoutStructure;

    protected array $handles;

    protected string $area;

    public function getLayoutStructure(): Config
    {
        return $this->layoutStructure;
    }

    public function getHandles(): array
    {
        return $this->handles;
    }

    public function setLayoutStructure(Config $layoutStructure): static
    {
        $this->layoutStructure = $layoutStructure;
        return $this;
    }

    public function setHandles(array $handles): static
    {
        $this->handles = $handles;
        return $this;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area): static
    {
        $this->area = $area;
        return $this;
    }
}
