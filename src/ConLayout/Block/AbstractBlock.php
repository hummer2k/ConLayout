<?php
namespace ConLayout\Block;

use Zend\Http\Request;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractBlock extends ViewModel implements
    BlockInterface,
    CacheableInterface
{
    const CACHE_KEY_PREFIX = 'block-';
    
    /**
     *
     * @var Request
     */
    protected $request;
    
    /**
     *
     * @var array
     */
    protected $cacheKeyInfo = array();
               
    /**
     * 
     * @param \Zend\Http\Request $request
     * @return AbstractBlock
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
    
    /**
     * 
     * @return Request
     */
    public function getRequest()
    {
        if (null === $this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }
    
    /**
     * set the cache key information
     * 
     * @param array $info
     * @return AbstractBlock
     */
    public function setCacheKeyInfo(array $info)
    {
        $this->cacheKeyInfo = $info;
        return $this;
    }
    
    /**
     * retrieve cache key information
     * 
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = array_merge([
            $this->getTemplate(),
            get_called_class()
        ], $this->cacheKeyInfo);
        return $cacheKeyInfo;
    }
    
    /**
     * add cache key info
     * 
     * @param string $info
     * @return AbstractBlock
     */
    public function addCacheKeyInfo($info)
    {
        $this->cacheKeyInfo[] = (string) $info;
        return $this;
    }
    
    /**
     * retrieve hashed cache key
     * 
     * @return string
     */
    public function getCacheKey()
    {
        return static::CACHE_KEY_PREFIX
            . md5(implode('|', $this->getCacheKeyInfo()));
    }
    
    /**
     * retrieve cache ttl in seconds
     * if false, block will not be cached
     * 
     * @return int|false
     */
    public function getCacheTtl()
    {
        return false;
    }
}
