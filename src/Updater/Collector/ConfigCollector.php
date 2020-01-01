<?php

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;

use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Config\Config;

class ConfigCollector implements CollectorInterface
{
    public const NAME = 'config';

    /**
     * @var array
     */
    private $config;

    /**
     * ConfigCollector constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function collect($handle, $area = null)
    {
        $areas = [
            LayoutUpdaterInterface::AREA_GLOBAL,
            $area
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
