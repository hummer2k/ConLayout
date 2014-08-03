<?php

namespace ConLayout\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface,
    \Zend\ServiceManager\ServiceLocatorAwareTrait,
    \ConLayout\Block\AbstractBlock;

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
     * @var LayoutService
     */
    protected $layoutService;
    
    /**
     * cache stores blocks as blockname => instance
     * 
     * @var array
     */
    protected $blocks = array();
    
    /**
     *
     * @var array
     */
    protected $createdBlocks;
    
    /**
     *
     * @var string
     */
    protected $defaultBlockClass = 'Zend\View\Model\ViewModel';
        
    /**
     * 
     * @param \ConLayout\Service\LayoutService $layoutService
     */
    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }
        
    /**
     * 
     * @param bool $force
     * @return \ConLayout\Service\BlocksBuilder
     */
    public function create($force = false)
    {
        if (null === $this->createdBlocks || $force) {
            $this->createdBlocks = $this->createBlocks();
        }
        return $this;
    }
    
    /**
     * 
     * @param \Zend\Config\Config $blockConfig
     * @return \Zend\Config\Config
     */
    protected function createBlocks(array $blockConfig = null)
    {
        if (null === $blockConfig) {
            $blockConfig = $this->layoutService->getBlockConfig();
        }
        foreach ($blockConfig as &$blocks) {
            foreach($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->createBlocks($block['children']);
                }
                $block['name'] = $blockName;
                $block['instance'] = $this->createBlock($block);
                // add block to cache so we have fast access 
                // to the block instance by $blockName
                $this->blocks[$blockName] = $block['instance'];
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
     * creates block instance from config
     * 
     * @param type $blockConfig
     * @return \ConLayout\Service\className
     */
    protected function createBlock(array $blockConfig)
    {
        $className = isset($blockConfig['class']) 
            ? $blockConfig['class']
            : $this->defaultBlockClass;
        /* @var $block \Zend\View\Model\ViewModel */
        if ($this->serviceLocator->has($className)) {
            $block = $this->serviceLocator->create($className);
        } else {
            $block = new $className();
        }
        
        if ($block instanceof AbstractBlock) {
            $request = $this->serviceLocator->get('Request');
            if ($request instanceof Zend\Http\Request) {
                $block->setRequest($request);
            }
        }
        
        // set template if configured
        if (isset($blockConfig['template'])) {
            $block->setTemplate($blockConfig['template']);
        }
        // set options
        if (isset($blockConfig['options'])) {
            $block->setOptions($blockConfig['options']);
        }
        // call block's configured methods if exists
        if (isset($blockConfig['actions']) && is_array($blockConfig['actions'])) {
            foreach ($blockConfig['actions'] as $method => $params) {
                if (method_exists($block, $method)) {
                    call_user_func_array(array($block, $method), $params);
                }
            }
        }
        $blockVars = isset($blockConfig['vars']) ? $blockConfig['vars'] : array();
        // inject variables   
        $block->setVariables(
            array_merge(
                (array) $block->getVariables(), 
                $blockVars,
                array(
                    'block' => $block,
                    'nameInLayout' => $blockConfig['name']
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
