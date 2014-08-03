<?php

namespace ConLayout\Service;

use Zend\Cache\Storage\StorageInterface,
    Zend\Stdlib\ArrayUtils;

/**
 * Config
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
     * @var array
     */
    protected $layoutConfig = array();
    
    /**
     *
     * @var array
     */
    protected $globalLayoutConfig = array();
        
    /**
     *
     * @var boolean
     */
    protected $isCacheEnabled = false;
    
    /**
     *
     * @var Config\SorterInterface
     */
    protected $sorter;
    
    /**
     * @var StorageInterface
     */
    protected $cache;
        
    /**
     * 
     * @param \ConLayout\Service\Config\CollectorInterface $configCollector
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @param \ConLayout\Service\Config\SorterInterface $sorter
     */
    public function __construct(
        Config\CollectorInterface $configCollector, 
        StorageInterface $cache,
        Config\SorterInterface $sorter
    )
    {
        $this->configCollector = $configCollector;
        $this->cache = $cache;
        $this->sorter = $sorter;
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
            if (!in_array($handle, $this->handles) && trim($handle) !== '') {
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
     * retrieve global merged layout config 
     * config is sorted by handle priority
     * 
     * @return array
     */
    public function getGlobalLayoutConfig()
    {
        if (empty($this->globalLayoutConfig)) {
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
        return $this->globalLayoutConfig;
    }
    
    /**
     * 
     * @return array
     */
    public function getLayoutConfig()
    {
        if (empty($this->layoutConfig)) {
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
        if (!isset($layoutConfig['blocks'])) {
            return $blockConfig;
        }
        $blockConfig = $layoutConfig['blocks'];
        
        if (isset($blockConfig['_remove'])) {
            $blockConfig = $this->removeBlocks($blockConfig, $blockConfig['_remove']);
            unset($blockConfig['_remove']);
        }
        $blockConfig = $this->sortBlocks($blockConfig);
        $this->cache->setItem($this->getBlocksCacheKey(), $blockConfig);
        return $blockConfig;
    }
    
    /**
     * 
     * @param array $blockConfig
     * @param array|string $blocksToRemove
     * @return array
     */
    protected function removeBlocks(array $blockConfig, $blocksToRemove)
    {
        if (!is_array($blocksToRemove)) {
            $blocksToRemove = array($blocksToRemove => true);
        }
        foreach($blockConfig as $captureTo => &$blocks) {
            if ($captureTo[0] === '_') continue;
            foreach ($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->removeBlocks($block['children'], $blocksToRemove);
                }
                foreach ($blocksToRemove as $removeBlock => $remove) {
                    if (false !== $remove && $blockName === $removeBlock) {
                        unset($blockConfig[$captureTo][$blockName]);
                    }
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
    protected function sortBlocks(array $blockConfig)
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
     * @param array|string $handleNames
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
     * @param Config\CollectorInterface $configCollector
     * @return \ConLayout\Service\Config
     */
    public function setConfigCollector(Config\CollectorInterface $configCollector)
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
    
    /**
     * 
     * @param array $layoutConfig
     * @return \ConLayout\Service\Config
     */
    public function setLayoutConfig(array $layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;
        return $this;
    }
    
    /**
     * reset service
     * 
     * @return \ConLayout\Service\LayoutService
     */
    public function reset()
    {
        $this->layoutConfig = array();
        $this->globalLayoutConfig = array();
        $this->handles = array('default');
        return $this;
    }
}
