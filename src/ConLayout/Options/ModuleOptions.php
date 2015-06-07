<?php

namespace ConLayout\Options;

use ConLayout\Listener\LayoutUpdateListener;
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
     * @var array
     */
    protected $assetPreparers = [];

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
     *
     * @return string
     */
    public function getDefaultArea()
    {
        if (!$this->defaultArea) {
            $this->defaultArea = LayoutUpdateListener::AREA_DEFAULT;
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
     * @return bool
     */
    public function getEnableDebug()
    {
        return $this->enableDebug;
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
     *
     * @return array
     */
    public function getAssetPreparers()
    {
        return $this->assetPreparers;
    }

    /**
     *
     * @param bool $enableDebug
     * @return ModuleOptions
     */
    public function setEnableDebug($enableDebug)
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
     * @param array $assetPreparers
     * @return ModuleOptions
     */
    public function setAssetPreparers(array $assetPreparers)
    {
        $this->assetPreparers = $assetPreparers;
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
}
