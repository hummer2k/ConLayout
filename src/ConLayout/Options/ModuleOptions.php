<?php

namespace ConLayout\Options;

use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Stdlib\AbstractOptions;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ModuleOptions extends AbstractOptions
{
    /**
     *
     * @var boolean
     */
    protected $enableDebug = false;

    /**
     *
     * @var array
     */
    protected $viewHelpers = [];

    /**
     *
     * @var string
     */
    protected $cacheBusterInternalBaseDir = './public';

    /**
     *
     * @var array
     */
    protected $layoutUpdatePaths = [];

    /**
     *
     * @var array
     */
    protected $layoutUpdateExtensions = ['php' => 'php'];

    /**
     *
     * @var array
     */
    protected $blockDefaults = [];

    /**
     *
     * @var array
     */
    protected $defaultArea;

    /**
     * Array of controller namespace -> action handle mappings.
     *
     * @var array
     */
    protected $controllerMap = [];

    /**
     * Flag to force the use of the route match controller param.
     *
     * @var boolean
     */
    protected $preferRouteMatchController = false;

    /**
     * Listeners to attach to EVM
     *
     * @var array
     */
    protected $listeners = [
        'ConLayout\Listener\ActionHandlesListener'  => true,
        'ConLayout\Listener\LayoutUpdateListener'   => true,
        'ConLayout\Listener\LoadLayoutListener'     => true,
        'ConLayout\Listener\LayoutTemplateListener' => true,
        'ConLayout\Listener\ViewHelperListener'     => true,
        'ConLayout\Listener\PrepareActionViewModelListener' => true
    ];

    /**
     * Retrieve an array of controller namespace -> action handle mappings.
     *
     * @return array
     */
    public function getControllerMap()
    {
        return $this->controllerMap;
    }

    /**
     * Set an array of controller namespace -> action handle mappings.
     *
     * @param array $controllerMap
     * @return ModuleOptions
     */
    public function setControllerMap(array $controllerMap)
    {
        $this->controllerMap = $controllerMap;

        return $this;
    }

    /**
     * Whether to force the use of the route match controller param.
     *
     * @return boolean
     */
    public function isPreferRouteMatchController()
    {
        return $this->preferRouteMatchController;
    }

    /**
     * Set whether to force the use of the route match controller param.
     *
     * @param boolean $preferRouteMatchController
     * @return ModuleOptions
     */
    public function setPreferRouteMatchController($preferRouteMatchController)
    {
        $this->preferRouteMatchController = (boolean) $preferRouteMatchController;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDefaultArea()
    {
        if (!$this->defaultArea) {
            $this->defaultArea = LayoutUpdaterInterface::AREA_DEFAULT;
        }
        return $this->defaultArea;
    }

    /**
     *
     * @param string $defaultArea
     * @return ModuleOptions
     */
    public function setDefaultArea($defaultArea)
    {
        $this->defaultArea = $defaultArea;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getViewHelpers()
    {
        return $this->viewHelpers;
    }

    /**
     * just here for bc
     *
     * @codeCoverageIgnore
     * @param bool $enableDebug
     * @return ModuleOptions
     */
    public function setEnableDebug($enableDebug = false)
    {
        $this->enableDebug = $enableDebug;
        return $this;
    }

    /**
     *
     * @param array $viewHelpers
     * @return ModuleOptions
     */
    public function setViewHelpers(array $viewHelpers)
    {
        $this->viewHelpers = $viewHelpers;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCacheBusterInternalBaseDir()
    {
        return $this->cacheBusterInternalBaseDir;
    }

    /**
     *
     * @param string $cacheBusterInternalBaseDir
     * @return ModuleOptions
     */
    public function setCacheBusterInternalBaseDir($cacheBusterInternalBaseDir)
    {
        $this->cacheBusterInternalBaseDir = $cacheBusterInternalBaseDir;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getLayoutUpdatePaths()
    {
        return $this->layoutUpdatePaths;
    }

    /**
     *
     * @return array
     */
    public function getLayoutUpdateExtensions()
    {
        return $this->layoutUpdateExtensions;
    }

    /**
     *
     * @param array $layoutUpdatePaths
     * @return ModuleOptions
     */
    public function setLayoutUpdatePaths(array $layoutUpdatePaths)
    {
        $this->layoutUpdatePaths = $layoutUpdatePaths;
        return $this;
    }

    /**
     *
     * @param array $layoutUpdateExtensions
     * @return ModuleOptions
     */
    public function setLayoutUpdateExtensions(array $layoutUpdateExtensions)
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

    /**
     *
     * @return array
     */
    public function getBlockDefaults()
    {
        return $this->blockDefaults;
    }

    /**
     *
     * @param array $blockDefaults
     * @return ModuleOptions
     */
    public function setBlockDefaults(array $blockDefaults)
    {
        $this->blockDefaults = $blockDefaults;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     *
     * @param array $listeners
     * @return ModuleOptions
     */
    public function setListeners(array $listeners)
    {
        $this->listeners = $listeners;
        return $this;
    }
}
