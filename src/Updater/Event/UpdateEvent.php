<?php

declare(strict_types=1);

namespace ConLayout\Updater\Event;

use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Config\Config;
use Laminas\EventManager\Event;

/**
 * @template-extends Event<object|string, array|ArrayAccess|object>
 */
class UpdateEvent extends Event
{
    public const string EVENT_COLLECT      = 'collect';
    public const string EVENT_COLLECT_POST = 'collect.post';

    protected Config $layoutStructure;

    protected array $handles;

    protected string $area;

    public function __construct()
    {
        parent::__construct();
        $this->layoutStructure = new Config([], true);
        $this->handles = [];
        $this->area = LayoutUpdaterInterface::AREA_DEFAULT;
    }

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
