<?php

namespace ConLayout\Listener;

use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Stdlib\Exception\RuntimeException;
use Zend\Stdlib\Glob;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     *
     * @var string
     */
    protected $area;

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
    public function __construct(
        array $paths = [],
        array $extensions = ['php'],
        $defaultArea = LayoutUpdaterInterface::AREA_DEFAULT
    ) {
        $this->paths = $paths;
        $this->extensions = $extensions;
        $this->area = $defaultArea;
    }

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(
            LayoutUpdater::class,
            'getLayoutStructure.pre',
            [$this, 'fetch']
        );
    }

    /**
     *
     * @param UpdateEvent $event
     */
    public function fetch(UpdateEvent $event)
    {
        $handles = $event->getHandles();
        if ($area = $event->getArea()) {
            $this->setArea($area);
        }
        $this->layoutStructure = $event->getLayoutStructure();
        foreach ($handles as $handle) {
            $this->fetchHandle($handle);
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
            try {
                $configFiles = Glob::glob($globPath, Glob::GLOB_BRACE);
            } catch (RuntimeException $e) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            foreach ($configFiles as $configFile) {
                $config = ConfigFactory::fromFile($configFile, true);
                if ($includeHandles = $config->get(LayoutUpdaterInterface::INSTRUCTION_INCLUDE)) {
                    $this->fetchIncludes($includeHandles);
                }
                $this->layoutStructure->merge($config);
            }
        }
    }

    /**
     * fetches includes recursively
     *
     * @param Config $includeHandles
     */
    protected function fetchIncludes(Config $includeHandles)
    {
        foreach ($includeHandles as $includeHandle) {
            $this->fetchHandle($includeHandle);
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
            LayoutUpdaterInterface::AREA_GLOBAL,
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
