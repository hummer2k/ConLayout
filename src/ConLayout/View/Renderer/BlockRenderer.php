<?php
namespace ConLayout\View\Renderer;

use ConLayout\Block\CacheableInterface,
    Traversable,
    Zend\Cache\Storage\StorageInterface,
    Zend\View\Renderer\PhpRenderer,
    Zend\EventManager\EventManagerAwareInterface,
    Zend\EventManager\EventManagerAwareTrait;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRenderer
    extends PhpRenderer
    implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    
    /**
     *
     * @var StorageInterface
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
        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, compact($nameOrModel));
        $success = false;
        if ($this->isCacheEnabled() && $nameOrModel instanceof CacheableInterface) {
            $cachedResult = $this->cache->getItem($nameOrModel->getCacheKey(), $success);
        } 
        if ($success) {
            $result = $cachedResult;
        } else {
            $result = parent::render($nameOrModel, $values);
        } 
        if ($this->isCacheEnabled() && !$success && $nameOrModel instanceof CacheableInterface) {
            // set cache ttl per item workaround
            // @see https://github.com/zendframework/zf2/pull/5386
            $options    = $this->cache->getOptions();
            $defaultTtl = $options->getTtl();
            $blockTtl   = $nameOrModel->getCacheTtl();
            if (false !== $blockTtl) {
                $options->setTtl($blockTtl);
                $this->cache->setItem($nameOrModel->getCacheKey(), $result);
                $options->setTtl($defaultTtl);
            }
        }
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, compact($result));
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
     * @return BlockRenderer
     */
    public function setCacheEnabled($flag = true)
    {
        $this->cacheEnabled = (bool) $flag;
        return $this;
    }
    
    /**
     * retrieve cache instance
     * 
     * @return StorageInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * set cache instance
     * 
     * @param StorageInterface $cache
     * @return BlockRenderer
     */
    public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }
}