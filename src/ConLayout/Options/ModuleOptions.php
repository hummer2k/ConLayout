<?php

namespace ConLayout\Options;

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

    public function getEnableDebug()
    {
        return $this->enableDebug;
    }

    public function getViewHelpers()
    {
        return $this->viewHelpers;
    }

    public function getAssetPreparers()
    {
        return $this->assetPreparers;
    }

    public function setEnableDebug($enableDebug)
    {
        $this->enableDebug = $enableDebug;
        return $this;
    }

    public function setViewHelpers($viewHelpers)
    {
        $this->viewHelpers = $viewHelpers;
        return $this;
    }

    public function setAssetPreparers($assetPreparers)
    {
        $this->assetPreparers = $assetPreparers;
        return $this;
    }

    public function getCacheBusterInternalBaseDir()
    {
        return $this->cacheBusterInternalBaseDir;
    }

    public function setCacheBusterInternalBaseDir($cacheBusterInternalBaseDir)
    {
        $this->cacheBusterInternalBaseDir = $cacheBusterInternalBaseDir;
        return $this;
    }

    public function getLayoutUpdatePaths()
    {
        return $this->layoutUpdatePaths;
    }

    public function getLayoutUpdateExtensions()
    {
        return $this->layoutUpdateExtensions;
    }

    public function setLayoutUpdatePaths(array $layoutUpdatePaths)
    {
        $this->layoutUpdatePaths = $layoutUpdatePaths;
        return $this;
    }

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
}
