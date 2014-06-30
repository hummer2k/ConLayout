<?php

namespace ConLayout\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\Config\Config as ZendConfig,
    \Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * BlocksBuilder
 *
 * @author hummer 
 */
class BlocksBuilder
    implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     *
     * @var Config
     */
    protected $layoutConfig;
    
    /**
     * cache stores blocks as blockname => instance
     * 
     * @var array
     */
    protected $blocks = array();
    
    /**
     *
     * @var ZendConfig
     */
    protected $createdBlocks;
    
    /**
     *
     * @var string
     */
    protected $defaultBlockClass = 'Zend\View\Model\ViewModel';
        
    /**
     * 
     * @param \ConLayout\Service\Config $layoutConfig
     */
    public function __construct(Config $layoutConfig)
    {
        $this->layoutConfig = $layoutConfig;
    }
        
    /**
     * 
     * @param bool $force
     * @return \ConLayout\Service\BlocksBuilder
     */
    public function create($force = false)
    {
        if (null === $this->createdBlocks && !$force) {
            $this->createdBlocks = $this->createBlocks();
        }
        return $this;
    }
    
    /**
     * 
     * @param \Zend\Config\Config $blockConfig
     * @return \Zend\Config\Config
     */
    protected function createBlocks(ZendConfig $blockConfig = null)
    {
        if (null === $blockConfig) {
            $blockConfig = $this->layoutConfig->getBlockConfig();
        }
        foreach ($blockConfig as $blocks) {
            foreach($blocks as $blockName => $block) {
                if ($block->children) {
                    $block->children = $this->createBlocks($block->children);
                }
                $block->name = $blockName;
                $block->instance = $this->createBlock($block);
                $this->blocks[$blockName] = $block->instance;
            }
        }
        return $blockConfig;
    }
    
    /**
     * 
     * @return array
     */
    public function getCreatedBlocks()
    {
        if (null === $this->createdBlocks) {
            $this->create();
        }
        return $this->createdBlocks;
    }
    
    /**
     * 
     * @param type $blockConfig
     * @return \ConLayout\Service\className
     */
    protected function createBlock(ZendConfig $blockConfig)
    {
        $className = $blockConfig->class 
            ? $blockConfig->class
            : $this->defaultBlockClass;
        /* @var $block \Zend\View\Model\ViewModel */
        if ($this->serviceLocator->has($className)) {
            $block = $this->serviceLocator->create($className);
        } else {
            $block = new $className();
        }
        // set template if configured
        if ($blockConfig->template) {
            $block->setTemplate($blockConfig->template);
        }
        // set options
        if ($blockConfig->options) {
            $block->setOptions($blockConfig->options);
        }
        // call block's configured methods if exists
        foreach ($blockConfig->get('actions', array()) as $method => $params) {
            if (method_exists($block, $method)) {
                call_user_func_array(array($block, $method), $params->toArray());
            }
        }
        // inject variables        
        $block->setVariables(
            array_merge(
                (array) $block->getVariables(), 
                $blockConfig->get('vars', array()),
                array(
                    'block' => $block,
                    'nameInLayout' => $blockConfig->name
                )
            )
        );
        
        // call block's init method
        if (method_exists($block, 'init')) {
            $block->init();
        }
        
        return $block;
    }
    
    /**
     * retrieve all blocks
     * 
     * @return type
     */
    public function getBlocks()
    {
        $this->create();
        return $this->blocks;
    }
    
    /**
     * retrieve specified block
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getBlock($name, $default = null)
    {
        $this->create();
        return isset($this->blocks[$name])
            ? $this->blocks[$name]
            : $default;
    }
}
