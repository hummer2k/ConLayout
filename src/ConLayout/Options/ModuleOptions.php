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
    protected $enableDebug;

    /**
     *
     * @var string
     */
    protected $childCaptureTo;

    /**
     *
     * @var array
     */
    protected $viewHelpers;

    /**
     *
     * @var array
     */
    protected $assetPreparers;

    /**
     *
     * @var string
     */
    protected $cacheBusterInternalBaseDir;

    /**
     *
     * @var array
     */
    protected $updateListenerGlobPaths;

    public function getEnableDebug()
    {
        return $this->enableDebug;
    }

    public function getChildCaptureTo()
    {
        return $this->childCaptureTo;
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

    public function setChildCaptureTo($childCaptureTo)
    {
        $this->childCaptureTo = $childCaptureTo;
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

    public function getUpdateListenerGlobPaths()
    {
        return $this->updateListenerGlobPaths;
    }

    public function setCacheBusterInternalBaseDir($cacheBusterInternalBaseDir)
    {
        $this->cacheBusterInternalBaseDir = $cacheBusterInternalBaseDir;
        return $this;
    }

    public function setUpdateListenerGlobPaths($updateListenerGlobPaths)
    {
        $this->updateListenerGlobPaths = $updateListenerGlobPaths;
        return $this;
    }
}
