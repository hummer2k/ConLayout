<?php
namespace ConLayout\Block;

use Zend\View\Model\ViewModel,
    Zend\Http\Request;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractBlock
    extends ViewModel
    implements BlockInterface,
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
     * @param type $variables
     * @param type $options
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $this->setCacheKeyInfo(array(
            $this->getTemplate(),
            get_called_class()
        ));
    }
            
    /**
     * 
     * @param \Zend\Http\Request $request
     * @return \ConLayout\Block\AbstractBlock
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
     * @return \ConLayout\Block\AbstractBlock
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
        return $this->cacheKeyInfo;
    }
    
    /**
     * add cache key info
     * 
     * @param string $info
     * @return \ConLayout\Block\AbstractBlock
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
