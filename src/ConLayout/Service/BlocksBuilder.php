<?php

namespace ConLayout\Service;

use ConLayout\Block\AbstractBlock;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ViewModel;

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
     * @var array
     */
    protected $blockConfig = array();
    
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
     * @param bool $force
     * @return BlocksBuilder
     */
    public function create($force = false)
    {
        if (!$this->createdBlocks || $force) {
            $this->createdBlocks = $this->createBlocks();
        }
        return $this;
    }

    /**
     * 
     * @param array $blockConfig
     * @return array
     */
    public function createBlocks(array $blockConfig = null)
    {
        if (null === $blockConfig) {
            $blockConfig = $this->blockConfig;
        }
        foreach ($blockConfig as $captureTo => &$blocks) {
            foreach($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->createBlocks($block['children']);
                }
                $block['name'] = $blockName;
                if (!isset($block['instance']) || !$block['instance'] instanceof ViewModel) {
                    $block['instance'] = $this->createBlock($block);
                }
                $block['instance']->setCaptureTo($captureTo);
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
        if (!$this->createdBlocks) {
            $this->create();
        }
        return $this->createdBlocks;
    }

    protected function createBlockInstance(array $blockConfig)
    {
        $className = isset($blockConfig['class'])
            ? $blockConfig['class']
            : $this->defaultBlockClass;

        /* @var $block ViewModel */
        if ($this->serviceLocator->has($className) && $className !== $this->defaultBlockClass) {
            $block = $this->serviceLocator->get($className);
        } else {
            $block = new $className();
        }
        if ($block instanceof AbstractBlock) {
            $request = $this->serviceLocator->get('Request');
            if ($request instanceof Request) {
                $block->setRequest($request);
            }
        }
        return $block;
    }
   
    /**
     * creates block instance from config
     * 
     * @todo refactor
     * @param type $blockConfig
     * @return \ConLayout\Service\className
     */
    protected function createBlock(array $blockConfig)
    {
        $block = $this->createBlockInstance($blockConfig);

        $this->setTemplate($block, $blockConfig);
        $this->addOptions($block, $blockConfig);
        $this->applyActions($block, $blockConfig);
        $this->setVariables($block, $blockConfig);

        if (method_exists($block, 'init')) {
            $block->init();
        }
        
        return $block;
    }

    /**
     * 
     * @param ViewModel $block
     * @param array $blockConfig
     * @return BlocksBuilder
     */
    protected function setTemplate(ViewModel $block, array $blockConfig)
    {
        if (isset($blockConfig['template'])) {
            $block->setTemplate($blockConfig['template']);
        }
        return $this;
    }

    /**
     *
     * @param ViewModel $block
     * @param array $blockConfig
     * @return BlocksBuilder
     */
    protected function addOptions(ViewModel $block, array $blockConfig)
    {
        if (isset($blockConfig['options'])) {
            $block->setOptions($blockConfig['options']);
        }
        return $this;
    }

    /**
     *
     * @param ViewModel $block
     * @param array $blockConfig
     * @return BlocksBuilder
     */
    protected function applyActions(ViewModel $block, array $blockConfig)
    {
        // call block's configured methods if exists
        if (isset($blockConfig['actions']) && is_array($blockConfig['actions'])) {
            foreach ($blockConfig['actions'] as $method => $params) {
                if (method_exists($block, $method)) {
                    call_user_func_array(array($block, $method), $params);
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param ViewModel $block
     * @param array $blockConfig
     * @return BlocksBuilder
     */
    protected function setVariables(ViewModel $block, array $blockConfig)
    {
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
        return $this;
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
     * 
     * @param array $blockConfig
     * @return BlocksBuilder
     */
    public function setBlockConfig(array $blockConfig)
    {
        $this->blockConfig = $blockConfig;
        return $this;
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
