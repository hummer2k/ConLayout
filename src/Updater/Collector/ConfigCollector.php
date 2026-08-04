<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Config\Config;

class ConfigCollector implements CollectorInterface
{
    public const NAME = 'config';

    private array $config;

    /**
     * ConfigCollector constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function collect(string $handle, ?string $area = null): Config
    {
        $areas = [
            LayoutUpdaterInterface::AREA_GLOBAL,
            $area,
        ];
        $structure = new Config([], true);
        foreach ($areas as $area) {
            $config = isset($this->config[$area][$handle])
                ? (array) $this->config[$area][$handle]
                : [];
            $structure->merge(new Config($config, true));
        }
        return $structure;
    }
}
