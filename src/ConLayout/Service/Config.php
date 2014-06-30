<?php

namespace ConLayout\Service;

use Zend\Cache\Storage\StorageInterface,
    Zend\Config\Config as ZendConfig;

/**
 * Config
 *
 * @author hummer 
 */
class Config
{
    const LAYOUT_CACHE_KEY = 'con-layout-layout';
    
    const BLOCKS_CACHE_KEY = 'con-layout-blocks';
    
    /**
     *
     * @var array
     */
    protected $handles = array(
        'default'
    );
    
    /**
     *
     * @var 
     */
    protected $configCollector;
    
    /**
     *
     * @var ZendConfig
     */
    protected $layoutConfig;
        
    /**
     *
     * @var boolean
     */
    protected $isCacheEnabled = false;
    
    /**
     * @var StorageInterface
     */
    protected $cache;
    
    /**
     * 
     * @param \ConLayout\Service\Config\CollectorInterface $configCollector
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @param array $moduleLayoutConfig
     */
    public function __construct(
        Config\CollectorInterface $configCollector, 
        StorageInterface $cache,
        array $moduleLayoutConfig = null
    )
    {
        $this->configCollector = $configCollector;
        $this->cache = $cache;
        $this->moduleLayoutConfig = $moduleLayoutConfig;
        $this->layoutConfig = new ZendConfig(array(), true);
    }
    
    /**
     * 
     * @param array|string $handles
     * @return Config
     */
    public function addHandle($handles)
    {
        if (!is_array($handles)) {
            $handles = array($handles);
        }
        foreach ($handles as $handle) {
            if (!in_array($handle, $this->handles)) {
                $this->handles[] = $handle;
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param array|string $handles
     * @return \ConLayout\Service\Config
     */
    public function removeHandle($handles)
    {
        if (!is_array($handles)) {
            $handles = array($handles);
        }
        $this->handles = array_diff($this->handles, $handles);
        return $this;
    }
    
    /**
     * 
     * @return array
     */
    public function getLayoutConfig()
    {
        if (!$this->layoutConfig->count()) {
            $result = $this->cache->getItem($this->getLayoutCacheKey(), $success);
            if ($this->isCacheEnabled && $success) {
                $this->layoutConfig = $result;
                return $this->layoutConfig;
            }
            foreach ($this->configCollector->collect() as $configFile) {
                $tempConfigs = include $configFile;
                foreach ($tempConfigs as $handle => $tempConfig) {
                    $tempConfig = new ZendConfig($tempConfig, true);
                    $handles = $tempConfig->handles;
                    if (null === $handles) {
                        $handles = $handle;
                    } else if ($handles instanceof ZendConfig) {
                        $handles[] = $handle;
                    }
                    if ($this->isHandleAllowed($handles)) {
                        $this->layoutConfig->merge($tempConfig);
                    }
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
    public function getLayoutCacheKey()
    {        
        return self::LAYOUT_CACHE_KEY 
            . '-' 
            . md5(implode('', $this->getHandles()));
    }
    
    /**
     * 
     * @return string
     */
    public function getBlocksCacheKey()
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
        if (isset($layoutConfig['layout'])) {
            return $layoutConfig['layout'];
        }
        return null;
    }
    
    /**
     * 
     * @return \Zend\Config\Config|array
     */
    public function getBlockConfig()
    {
        $blockConfig = $this->cache->getItem($this->getBlocksCacheKey(), $success);
        if ($this->isCacheEnabled && $success) {
            return $blockConfig;
        }
        $blockConfig = array();
        $layoutConfig = $this->getLayoutConfig();
        if (!$layoutConfig->blocks) {
            return $blockConfig;
        }
        $blockConfig = $layoutConfig->blocks->toArray();
        
        if (isset($blockConfig['_remove'])) {
            $blockConfig = $this->removeBlocks($blockConfig, $blockConfig['_remove']);
            unset($blockConfig['_remove']);
        } 
        $blockConfig = new ZendConfig($this->sortBlocks($blockConfig), true);
        $this->cache->setItem($this->getBlocksCacheKey(), $blockConfig);
        return $blockConfig;
    }
    
    /**
     * 
     * @param array $blockConfig
     * @param array|string $blocksToRemove
     * @return array
     */
    public function removeBlocks(array $blockConfig, $blocksToRemove)
    {
        if (!is_array($blocksToRemove)) {
            $blocksToRemove = array($blocksToRemove);
        }
        foreach($blockConfig as $placeholderName => &$blocks) {
            foreach ($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->removeBlocks($block['children'], $blocksToRemove);
                }
                if (in_array($blockName, $blocksToRemove)) {
                    unset($blockConfig[$placeholderName][$blockName]);
                }
            }
        }
        return $blockConfig;
    }
    
    /**
     * 
     * @param array $blockConfig
     * @return array
     */
    public function sortBlocks(array $blockConfig)
    {
        foreach ($blockConfig as &$blocks) {
            foreach ($blocks as &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->sortBlocks($block['children']);
                }
            }
            uasort($blocks, function($a, $b) {
                $orderA = isset($a['order']) ? $a['order'] : 10;
                $orderB = isset($b['order']) ? $b['order'] : 10;                        
                if ($orderA == $orderB) {
                    return 0;
                }
                return ($orderA < $orderB) ? -1 : 1;
            });
        }
        return $blockConfig;
    }
    
    /**
     * 
     * @param array|string|ZendConfig $handleNames
     * @return boolean
     */
    protected function isHandleAllowed($handleNames)
    {
        if (is_string($handleNames)) {
            $handleNames = array($handleNames);
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
     * @return type
     */
    public function getConfigCollector()
    {
        return $this->configCollector;
    }

    /**
     * 
     * @param type $handles
     * @return \ConLayout\Service\Config
     */
    public function setHandles($handles)
    {
        $this->handles = $handles;
        return $this;
    }

    /**
     * 
     * @param type $configCollector
     * @return \ConLayout\Service\Config
     */
    public function setConfigCollector($configCollector)
    {
        $this->configCollector = $configCollector;
        return $this;
    }
    
    /**
     * 
     * @param bool $flag
     */
    public function setIsCacheEnabled($flag = true)
    {
        $this->isCacheEnabled = (bool) $flag;
    }
    
    /**
     * 
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->isCacheEnabled;
    }
}
