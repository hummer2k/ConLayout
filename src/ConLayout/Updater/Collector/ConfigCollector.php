<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;


use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;

class ConfigCollector implements CollectorInterface
{
    const NAME = 'config';

    /**
     * @var string
     */
    private $area;

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
    public function collect($handle)
    {
        $areas = [
            LayoutUpdaterInterface::AREA_GLOBAL,
            $this->area
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

    /**
     * @inheritDoc
     */
    public function setArea($area)
    {
        $this->area = $area;
    }
}
