<?php

declare(strict_types=1);

namespace ConLayout\Updater\Collector;

use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Config\Config;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\Stdlib\Exception\RuntimeException;
use Laminas\Stdlib\Glob;

class FilesystemCollector implements CollectorInterface
{
    public const NAME = 'filesystem';

    private array $paths = [];

    /**
     * list of file extensions to search for e.g. ['php', 'xml']
     */
    private array $extensions = [];

    private array $collectedFiles = [];

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
    public function collect(string $handle, ?string $area = null): Config
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
                $this->collectedFiles[$handle][] = $configFile;
            }
        }
        return $tempStructure;
    }

    private function getGlobPaths(string $handle, ?string $area = null): array
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

    public function getCollectedFiles(): array
    {
        return $this->collectedFiles;
    }
}
