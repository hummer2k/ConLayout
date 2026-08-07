<?php

declare(strict_types=1);

namespace ConLayout\Options;

use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Stdlib\AbstractOptions;
use Throwable;

class ModuleOptions extends AbstractOptions
{
    protected bool $debug = false;

    /**
     * @var bool|string
     */
    protected bool|string $remoteCall = false;

    protected array $viewHelpers = [];

    protected string $cacheBusterInternalBaseDir = './public';

    protected array $layoutUpdatePaths = [];

    protected array $layoutUpdateExtensions = ['php' => 'php'];

    protected array $blockDefaults = [];

    protected ?string $defaultArea = null;

    protected array $generators = [];

    protected array $collectors = [];

    protected array $xmlPreprocessor = [];

    protected string $bodyClassPrefix = '';

    protected bool $enabled = true;

    protected string $layoutTemplate = '';

    protected string $basePath = '';

    protected string $errorHandleAttribute = Throwable::class;

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;
        return $this;
    }

    public function getDefaultArea(): string
    {
        if (!$this->defaultArea) {
            $this->defaultArea = LayoutUpdaterInterface::AREA_DEFAULT;
        }
        return $this->defaultArea;
    }

    public function setDefaultArea(string $defaultArea): static
    {
        $this->defaultArea = $defaultArea;
        return $this;
    }

    public function getViewHelpers(): array
    {
        return $this->viewHelpers;
    }

    /**
     * just here for bc
     *
     * @codeCoverageIgnore
     */
    public function setEnableDebug(bool $enableDebug = false): static
    {
        $this->enableDebug = $enableDebug;
        return $this;
    }

    public function setViewHelpers(array $viewHelpers): static
    {
        $this->viewHelpers = $viewHelpers;
        return $this;
    }

    public function getCacheBusterInternalBaseDir(): string
    {
        return $this->cacheBusterInternalBaseDir;
    }

    public function setCacheBusterInternalBaseDir(string $cacheBusterInternalBaseDir): static
    {
        $this->cacheBusterInternalBaseDir = $cacheBusterInternalBaseDir;
        return $this;
    }

    public function getLayoutUpdatePaths(): array
    {
        return $this->layoutUpdatePaths;
    }

    public function getLayoutUpdateExtensions(): array
    {
        return $this->layoutUpdateExtensions;
    }

    public function setLayoutUpdatePaths(array $layoutUpdatePaths): static
    {
        $this->layoutUpdatePaths = $layoutUpdatePaths;
        return $this;
    }

    public function setLayoutUpdateExtensions(array $layoutUpdateExtensions): static
    {
        foreach ($layoutUpdateExtensions as $extension => $value) {
            if (false === $value) {
                continue;
            }
            if (is_string($value)) {
                $extension = $value;
            }
            $this->layoutUpdateExtensions[$extension] = $extension;
        }
        return $this;
    }

    public function getBlockDefaults(): array
    {
        return $this->blockDefaults;
    }

    public function setBlockDefaults(array $blockDefaults): static
    {
        $this->blockDefaults = $blockDefaults;
        return $this;
    }

    public function getGenerators(): array
    {
        return $this->generators;
    }

    public function setGenerators(array $generators): static
    {
        $this->generators = $generators;
        return $this;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function setCollectors(array $collectors): static
    {
        $this->collectors = $collectors;
        return $this;
    }

    public function getBodyClassPrefix(): string
    {
        return $this->bodyClassPrefix;
    }

    public function getXmlPreprocessor(): array
    {
        return $this->xmlPreprocessor;
    }

    public function setXmlPreprocessor(array $xmlPreprocessor): static
    {
        $this->xmlPreprocessor = $xmlPreprocessor;
        return $this;
    }

    public function setBodyClassPrefix(string $bodyClassPrefix): void
    {
        $this->bodyClassPrefix = $bodyClassPrefix;
    }

    public function getRemoteCall(): bool|string
    {
        return $this->remoteCall;
    }

    public function setRemoteCall(bool|string $remoteCall): void
    {
        $this->remoteCall = $remoteCall;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getLayoutTemplate(): string
    {
        return $this->layoutTemplate;
    }

    public function setLayoutTemplate(string $layoutTemplate): static
    {
        $this->layoutTemplate = $layoutTemplate;
        return $this;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): static
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function getErrorHandleAttribute(): string
    {
        return $this->errorHandleAttribute;
    }

    public function setErrorHandleAttribute(string $errorHandleAttribute): static
    {
        $this->errorHandleAttribute = $errorHandleAttribute;
        return $this;
    }
}
