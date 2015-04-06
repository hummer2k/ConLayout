<?php

namespace ConLayout\Service;

use ConLayout\Config\CollectorInterface;
use ConLayout\Config\Mutator\MutatorInterface;
use ConLayout\Config\SorterInterface;
use ConLayout\Handle\Handle;
use Zend\Cache\Storage\StorageInterface;
use Zend\Config\Config;
use Zend\Stdlib\ArrayUtils;

/**
 *
 * @author hummer 
 */
class LayoutService
{
    const LAYOUT_CACHE_KEY = 'con-layout-layout';
    
    const BLOCKS_CACHE_KEY = 'con-layout-blocks';
    
    const GLOBAL_LAYOUT_CACHE_KEY = 'con-layout-global';
    
    /**
     *
     * @var array
     */
    protected $handles = [];
    
    /**
     *
     * @var 
     */
    protected $configCollector;
    
    /**
     *
     * @var array
     */
    protected $layoutConfig = [];

    /**
     *
     * @var array
     */
    protected $globalLayoutConfig = [];
        
    /**
     *
     * @var boolean
     */
    protected $isCacheEnabled = false;
    
    /**
     *
     * @var SorterInterface
     */
    protected $sorter;
    
    /**
     * @var StorageInterface
     */
    protected $cache;

    /**
     *
     * @var MutatorInterface[]
     */
    protected $blockConfigMutators = [];

    /**
     * 
     * @param CollectorInterface $configCollector
     * @param StorageInterface $cache
     * @param SorterInterface $sorter
     */
    public function __construct(
        CollectorInterface $configCollector,
        StorageInterface $cache,
        SorterInterface $sorter
    )
    {
        $this->configCollector = $configCollector;
        $this->cache = $cache;
        $this->sorter = $sorter;
        $this->handles = [
            new Handle('default', -1)
        ];
    }
    
    /**
     * 
     * @param array|string $handles
     * @return Config
     */
    public function addHandle($handles)
    {
        if (!is_array($handles)) {
            $handles = [$handles];
        }
        foreach ($handles as $handle) {
            if (!in_array($handle, $this->handles) && trim($handle) !== '') {
                $this->handles[] = $handle;
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param array|string $handles
     * @return \ConLayout\Config
     */
    public function removeHandle($handles)
    {
        if (!is_array($handles)) {
            $handles = [$handles];
        }
        $this->handles = array_values(array_diff($this->handles, $handles));
        return $this;
    }
    
    /**
     * retrieve global merged layout config 
     * config is sorted by handle priority
     * 
     * @return array
     */
    public function getGlobalLayoutConfig()
    {
        if (empty($this->globalLayoutConfig)) {
            $success = false;
            $result = $this->cache->getItem(self::GLOBAL_LAYOUT_CACHE_KEY, $success);
            if ($this->isCacheEnabled && $success) {
                $this->globalLayoutConfig = $result;
                return $this->globalLayoutConfig;
            }
            foreach ($this->configCollector->collect() as $config) {                
                $this->globalLayoutConfig = ArrayUtils::merge(
                    $this->globalLayoutConfig,
                    $config
                );
            }
            $this->sorter->sort(
                $this->globalLayoutConfig
            );
            $this->cache->setItem(self::GLOBAL_LAYOUT_CACHE_KEY, $this->globalLayoutConfig);
        }
        $config = new Config([]);
        $config->
        return $this->globalLayoutConfig;
    }
    
    /**
     * 
     * @return array
     */
    public function getLayoutConfig()
    {
        if (empty($this->layoutConfig)) {
            $success = false;
            $result = $this->cache->getItem($this->getLayoutCacheKey(), $success);
            if ($this->isCacheEnabled && $success) {
                $this->layoutConfig = $result;
                return $this->layoutConfig;
            }
            $globalLayoutConfig = $this->getGlobalLayoutConfig();
            foreach ($globalLayoutConfig as $handle => $config) {
                $handles = isset($config['handles']) ? $config['handles'] : null;
                if (null === $handles) {
                    $handles = $handle;
                } else if (is_array($handles)) {
                    $handles[] = $handle;
                }
                if ($this->isHandleAllowed($handles)) {
                    $this->layoutConfig = ArrayUtils::merge(
                        $this->layoutConfig,
                        $config
                    );
                }
            }
            $this->cache->setItem($this->getLayoutCacheKey(), $this->layoutConfig);
        }
        return $this->layoutConfig;
    }
        
    /**
     * 
     * @return string
     */
    protected function getLayoutCacheKey()
    {        
        return self::LAYOUT_CACHE_KEY 
            . '-' 
            . md5(implode('', $this->getHandles()));
    }
    
    /**
     * 
     * @return string
     */
    protected function getBlocksCacheKey()
    {
        return self::BLOCKS_CACHE_KEY
            . '-' 
            . md5(implode('', $this->getHandles()));
    }
    
    /**
     * 
     * @return string|null
     */
    public function getLayoutTemplate()
    {
        $layoutConfig = $this->getLayoutConfig();
        if (!empty($layoutConfig['layout'])) {
            return $layoutConfig['layout'];
        }
        return null;
    }
    
    /**
     * 
     * @param MutatorInterface $blockConfigModifier
     * @return LayoutService
     */
    public function addBlockConfigMutator(MutatorInterface $blockConfigModifier)
    {
        $this->blockConfigMutators[] = $blockConfigModifier;
        return $this;
    }

    /**
     * 
     * @return Config|array
     */
    public function getBlockConfig()
    {
        $success = false;
        $blockConfig = $this->cache->getItem($this->getBlocksCacheKey(), $success);
        if ($this->isCacheEnabled && $success) {
            return $blockConfig;
        }
        $blockConfig = [];
        $layoutConfig = $this->getLayoutConfig();
        if (!isset($layoutConfig['blocks'])) {
            return $blockConfig;
        }
        $blockConfig = $layoutConfig['blocks'];

        foreach ($this->blockConfigMutators as $blockConfigModifier) {
            $blockConfig = $blockConfigModifier->mutate($blockConfig);
        }

        $this->cache->setItem($this->getBlocksCacheKey(), $blockConfig);
        return $blockConfig;
    }    
        
    /**
     * 
     * @param array|string $handleNames
     * @return boolean
     */
    protected function isHandleAllowed($handleNames)
    {
        if (is_string($handleNames)) {
            $handleNames = [$handleNames];
        }
        foreach ($handleNames as $handleName) {
            if (in_array($handleName, $this->handles)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @return array
     */
    public function getHandles()
    {
        return $this->handles;
    }

    /**
     * 
     * @param array $handles
     * @return LayoutService
     */
    public function setHandles(array $handles)
    {
        $this->handles = $handles;
        return $this;
    }
    
    /**
     * 
     * @param bool $flag
     */
    public function setIsCacheEnabled($flag = true)
    {
        $this->isCacheEnabled = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->isCacheEnabled;
    }
    
    /**
     * 
     * @param array $layoutConfig
     * @return LayoutService
     */
    public function setLayoutConfig(array $layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;
        return $this;
    }

    /**
     * reset service
     * 
     * @return LayoutService
     */
    public function reset()
    {
        $this->layoutConfig = [];
        $this->globalLayoutConfig = [];
        $this->handles = ['default'];
        return $this;
    }
}
