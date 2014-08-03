<?php
namespace ConLayout\View\Renderer;

use Zend\View\Renderer\PhpRenderer;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRenderer
    extends PhpRenderer
{
    /**
     *
     * @var \Zend\Cache\Storage\StorageInterface
     */
    protected $cache;
    
    /**
     * cache enabled flag
     * 
     * @var boolean
     */
    protected $cacheEnabled = false;
    
    /**
     * Overloading: proxy to Variables container 
     * if method getName() of block exists it will pull the data 
     * from the block  
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        $block = $this->plugin('viewModel')->getCurrent();
        if (is_callable(array($block, $method))) {
            return $block->{$method}();
        }
        return parent::__get($name);
    }
    
    /**
     * Processes a view script and returns the output.
     * overload: added caching mechanism
     *
     * @param  string|Model $nameOrModel Either the template to use, or a
     *                                   ViewModel. The ViewModel must have the
     *                                   template as an option in order to be
     *                                   valid.
     * @param  null|array|Traversable $values Values to use when rendering. If none
     *                                provided, uses those in the composed
     *                                variables container.
     * @return string The script output.
     * @throws Exception\DomainException if a ViewModel is passed, but does not
     *                                   contain a template option.
     * @throws Exception\InvalidArgumentException if the values passed are not
     *                                            an array or ArrayAccess object
     * @throws Exception\RuntimeException if the template cannot be rendered
     */
    public function render($nameOrModel, $values = null)
    {
        if ($this->isCacheEnabled() && $nameOrModel instanceof \ConLayout\Block\CacheableInterface) {
            $result = $this->cache->getItem($nameOrModel->getCacheKey(), $success);
            if ($success) {
                return $result;
            }
        }
        $result = parent::render($nameOrModel, $values);
        if ($this->isCacheEnabled() && $nameOrModel instanceof \ConLayout\Block\CacheableInterface) {
            $this->cache->setItem($nameOrModel->getCacheKey(), $result);
        }
        return $result;
    }
    
    /**
     * check if cache is enabled
     * 
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }
    
    /**
     * enable/disable cache
     * 
     * @param bool $flag
     * @return \ConLayout\View\Renderer\BlockRenderer
     */
    public function setCacheEnabled($flag = true)
    {
        $this->cacheEnabled = (bool) $flag;
        return $this;
    }
    
    /**
     * retrieve cache instance
     * 
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * set cache instance
     * 
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @return \ConLayout\View\Renderer\BlockRenderer
     */
    public function setCache(\Zend\Cache\Storage\StorageInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }
}
