<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Config\Config;

readonly class ConfigCollector implements CollectorInterface
{
    public const string NAME = 'config';

    public function __construct(private array $config) {}

    public function collect(string $handle, ?string $area = null): Config
    {
        $areas = [
            LayoutUpdaterInterface::AREA_GLOBAL,
            $area,
        ];
        $structure = new Config([], true);
        foreach ($areas as $area) {
            if (null === $area) {
                continue;
            }
            $config = isset($this->config[$area][$handle])
                ? (array) $this->config[$area][$handle]
                : [];
            $structure->merge(new Config($config, true));
        }
        return $structure;
    }
}
