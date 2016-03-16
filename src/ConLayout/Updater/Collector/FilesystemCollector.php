<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;


use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\Stdlib\Exception\RuntimeException;
use Zend\Stdlib\Glob;
use Zend\Config\Factory as ConfigFactory;

class FilesystemCollector implements CollectorInterface
{
    const NAME = 'filesystem';

    /**
     *
     * @var string
     */
    private $area;

    /**
     *
     * @var array
     */
    private $paths = [];

    /**
     * list of file extensions to search for e.g. ['php', 'xml']
     *
     * @var array
     */
    private $extensions = [];

    /**
     *
     * @param array $paths
     * @param array $extensions
     */
    public function __construct(
        array $paths = [],
        array $extensions = ['php', 'xml']
    ) {
        $this->paths = $paths;
        $this->extensions = $extensions;
    }


    /**
     * @inheritDoc
     */
    public function init(array $handles)
    {
        // no need to initialize
        return;
    }

    /**
     * @inheritDoc
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @inheritDoc
     */
    public function fetchHandle($handle)
    {
        $globPaths = $this->getGlobPaths($handle);
        $tempStructure = new Config([], true);
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
                $tempStructure->merge($config);
            }
        }
        return $tempStructure;
    }

    /**
     *
     * @param string $handle
     * @return array
     */
    private function getGlobPaths($handle)
    {
        $globPaths = [];
        $areas = [
            LayoutUpdaterInterface::AREA_GLOBAL,
            $this->area
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
}
