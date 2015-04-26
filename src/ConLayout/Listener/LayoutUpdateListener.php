<?php

namespace ConLayout\Listener;

use ConLayout\Updater\Event\UpdateEvent;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListener implements ListenerAggregateInterface
{
    const AREA_GLOBAL = 'global';

    use ListenerAggregateTrait;

    /**
     *
     * @var array
     */
    protected $globPaths;

    /**
     *
     * @var string
     */
    protected $area = self::AREA_GLOBAL;

    /**
     *
     * @param array $globPaths
     *
     * format: [
     *  'area' => [
     *      'default' => '/path/to/layout-updates.{xml,php}'
     *  ]
     */
    public function __construct(array $globPaths)
    {
        $this->globPaths = $globPaths;
    }

    /**
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     *
     * @param string $area
     * @return LayoutUpdateListener
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            [$this, 'onLoadGlobalLayoutStructure']
        );
    }

    /**
     *
     * @param UpdateEvent $e
     */
    public function onLoadGlobalLayoutStructure(UpdateEvent $e)
    {
        $globalLayoutStructure = $e->getGlobalLayoutStructure();
        $globPaths = $this->getGlobPaths();
        foreach ($globPaths as $globPath) {
            foreach (Glob::glob($globPath, Glob::GLOB_BRACE) as $config) {
                $globalLayoutStructure->merge(
                    ConfigFactory::fromFile($config, true)
                );
            }
        }
    }

    /**
     * retrieve merged glob paths
     *
     * @return array
     */
    protected function getGlobPaths()
    {
        $globPaths = [];
        $areas = [
            self::AREA_GLOBAL,
            $this->getArea()
        ];
        foreach($areas as $area) {
            if (isset($this->globPaths[$area])) {
                $globPaths = ArrayUtils::merge(
                    $globPaths,
                    array_values((array) $this->globPaths[$area])
                );
            }
        }
        return $globPaths;
    }
}
