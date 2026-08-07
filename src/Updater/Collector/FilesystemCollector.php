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
    public const string NAME = 'filesystem';

    private array $collectedFiles = [];

    public function __construct(
        private readonly array $paths = [],
        private readonly array $extensions = ['php', 'xml'],
        private readonly ?XmlPreprocessor $xmlPreprocessor = null,
    ) {}

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
                if (!$config instanceof Config) {
                    $config = new Config($config, true);
                }
                if (null !== $this->xmlPreprocessor && str_ends_with($configFile, '.xml')) {
                    $config = $this->xmlPreprocessor->process($config);
                }
                $tempStructure->merge($config);
                $this->collectedFiles[$handle][] = $configFile;
            }
        }
        return $tempStructure;
    }

    private function getGlobPaths(string $handle, ?string $area = null): array
    {
        $globPaths = [];
        $areas = array_unique([
            LayoutUpdaterInterface::AREA_GLOBAL,
            $area,
        ]);
        foreach ($areas as $areaName) {
            if (null !== $areaName && isset($this->paths[$areaName])) {
                foreach ($this->paths[$areaName] as $basePath) {
                    $globPaths[] = sprintf(
                        '%s/%s.{%s}',
                        $basePath,
                        $handle,
                        implode(',', $this->extensions),
                    );
                }
            }
        }
        return array_unique($globPaths);
    }

    public function getCollectedFiles(): array
    {
        return $this->collectedFiles;
    }
}
