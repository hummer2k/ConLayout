<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Updater\Collector;

use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Config\Config;
use Zend\Config\Factory as ConfigFactory;
use Zend\Stdlib\Exception\RuntimeException;
use Zend\Stdlib\Glob;

class FilesystemCollector implements CollectorInterface
{
    const NAME = 'filesystem';

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
    public function collect($handle, $area = null)
    {
        $globPaths = $this->getGlobPaths($handle, $area);
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
     * @param null|string $area
     * @return array
     */
    private function getGlobPaths($handle, $area = null)
    {
        $globPaths = [];
        $areas = [
            LayoutUpdaterInterface::AREA_GLOBAL,
            $area
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
