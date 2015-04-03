<?php

namespace ConLayout\Service;

use ConLayout\Block\AbstractBlock;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * BlocksBuilder
 *
 * @author hummer 
 */
class BlocksBuilder implements ServiceLocatorAwareInterface
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
    protected $blocksCache = [];

    /**
     *
     * @var string
     */
    protected $defaultBlockClass = 'Zend\View\Model\ViewModel';

    /**
     *
     * @param array $blockConfig
     * @param ModelInterface $parent
     * @return type
     */
    public function createBlocks(array $blockConfig = null)
    {
        if (null === $blockConfig) {
            $blockConfig = $this->blockConfig;
        }
        foreach ($blockConfig as $captureTo => &$blocks) {
            foreach ($blocks as $blockName => &$block) {
                if (isset($block['children'])) {
                    $block['children'] = $this->createBlocks($block['children']);
                }
                if (!isset($block['instance']) || !$block['instance'] instanceof ViewModel) {
                    $block['instance'] = $this->createBlock($blockName, $block);
                }
                $block['instance']->setCaptureTo($captureTo);
                // add block to cache so we have fast access
                // to the block instance by $blockName
                $this->addBlock($blockName, $block['instance']);
            }
        }
        return $blockConfig;
    }

    /**
     *
     * @param string $name
     * @param ModelInterface $block
     * @return \ConLayout\Service\BlocksBuilder
     */
    protected function addBlock($name, ModelInterface $block)
    {
        $this->blocksCache[$name] = $block;
        return $this;
    }

    /**
     *
     * @param array $specs
     * @return AbstractBlock
     */
    protected function createBlockInstance(array $specs)
    {
        $className = isset($specs['class'])
            ? $specs['class']
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
     * @param string $name
     * @param array|ModelInterface $specs
     * @return ModelInterface 
     */
    protected function createBlock($name, $specs)
    {
        if (!$specs instanceof ModelInterface) {
            $block = $this->createBlockInstance($specs);
        }
        $block->setVariable('nameInLayout', $name);

        $this->setTemplate($block, $specs);
        $this->addOptions($block, $specs);
        $this->applyActions($block, $specs);
        $this->setVariables($block, $specs);

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
        $blockVars = isset($blockConfig['vars'])
            ? $blockConfig['vars']
            : array();
        foreach ($blockVars as $key => $value) {
            $block->setVariable($key, $value);
        }
        $block->setVariable('block', $block);
        return $this;
    }

    /**
     * retrieve all blocks
     * 
     * @return type
     */
    public function getBlocks()
    {
        return $this->blocksCache;
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
        return isset($this->blocksCache[$name])
            ? $this->blocksCache[$name]
            : $default;
    }
}