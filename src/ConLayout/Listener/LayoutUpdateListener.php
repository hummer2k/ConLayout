<?php

namespace ConLayout\Listener;

use ConLayout\Updater\Event\FetchEvent;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
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
     * @var string
     */
    protected $area = self::AREA_GLOBAL;

    /**
     *
     * @var array
     */
    protected $paths = [];

    /**
     * list of file extensions to search for e.g. ['php', 'xml']
     *
     * @var array
     */
    protected $extensions = [];

    /**
     *
     * @var Config
     */
    protected $layoutStructure;

    /**
     *
     * @param array $paths
     * @param array $extensions
     */
    public function __construct(array $paths = [], array $extensions = ['php'])
    {
        $this->paths = $paths;
        $this->extensions = $extensions;
    }

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'fetch',
            [$this, 'fetch']
        );
    }

    /**
     *
     * @param FetchEvent $event
     */
    public function fetch(FetchEvent $event)
    {
        $handle = $event->getHandle();
        $this->layoutStructure = $event->getLayoutStructure();
        $this->fetchHandle($handle);
        $this->cleanUpLayoutStructure();
    }

    /**
     * removes unnecessary layout instructions
     */
    protected function cleanUpLayoutStructure()
    {
        if (isset($this->layoutStructure[LayoutUpdaterInterface::INSTRUCTION_INCLUDE])) {
            unset($this->layoutStructure[LayoutUpdaterInterface::INSTRUCTION_INCLUDE]);
        }
    }

    /**
     *
     * @param string $handle
     */
    protected function fetchHandle($handle)
    {
        $globPaths = $this->getGlobPaths($handle);
        foreach ($globPaths as $globPath) {
            $configFiles = Glob::glob($globPath, Glob::GLOB_BRACE);
            foreach ($configFiles as $configFile) {
                $config = ConfigFactory::fromFile($configFile, true);
                if ($includeHandles = $config->get(LayoutUpdaterInterface::INSTRUCTION_INCLUDE)) {
                    foreach ($includeHandles as $includeHandle) {
                        $this->fetchHandle($includeHandle);
                    }
                }
                $this->layoutStructure->merge($config);
            }
        }
    }

    /**
     *
     * @param string $handle
     * @return array
     */
    protected function getGlobPaths($handle)
    {
        $globPaths = [];
        $areas = [
            self::AREA_GLOBAL,
            $this->getArea()
        ];
        $areas = array_unique($areas);
        foreach ($areas as $area) {
            if (isset($this->paths[$area])) {
                foreach ($this->paths[$area] as $basePath) {
                    $globPaths[] = sprintf(
                        '%s/%s.{%s}',
                        $basePath,
                        $handle,
                        implode(',', $this->extensions)
                    );
                }
            }
        }
        array_unique($globPaths);
        return $globPaths;
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
}
