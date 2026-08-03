<?php

declare(strict_types=1);

namespace ConLayout\Options;

use ConLayout\Listener\ActionHandlesListener;
use ConLayout\Listener\BodyClassListener;
use ConLayout\Listener\LayoutTemplateListener;
use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Listener\LoadLayoutListener;
use ConLayout\Listener\PrepareActionViewModelListener;
use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\Stdlib\AbstractOptions;

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

    /**
     * Array of controller namespace -> action handle mappings.
     */
    protected array $controllerMap = [];

    /**
     * Flag to force the use of the route match controller param.
     */
    protected bool $preferRouteMatchController = false;

    protected array $generators = [];

    protected array $collectors = [];

    protected string $bodyClassPrefix = '';

    /**
     * Listeners to attach to EVM
     */
    protected array $listeners = [
        ActionHandlesListener::class  => true,
        BodyClassListener::class      => true,
        LoadLayoutListener::class     => true,
        PrepareActionViewModelListener::class => true
    ];

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Retrieve an array of controller namespace -> action handle mappings.
     */
    public function getControllerMap(): array
    {
        return $this->controllerMap;
    }

    /**
     * Set an array of controller namespace -> action handle mappings.
     */
    public function setControllerMap(array $controllerMap): static
    {
        $this->controllerMap = $controllerMap;

        return $this;
    }

    /**
     * Whether to force the use of the route match controller param.
     */
    public function isPreferRouteMatchController(): bool
    {
        return $this->preferRouteMatchController;
    }

    /**
     * Set whether to force the use of the route match controller param.
     */
    public function setPreferRouteMatchController(bool $preferRouteMatchController): static
    {
        $this->preferRouteMatchController = $preferRouteMatchController;

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

    public function getListeners(): array
    {
        return $this->listeners;
    }

    public function setListeners(array $listeners): static
    {
        $this->listeners = $listeners;
        return $this;
    }

    public function getBodyClassPrefix(): string
    {
        return $this->bodyClassPrefix;
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
}
