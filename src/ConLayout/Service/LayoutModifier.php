<?php
namespace ConLayout\Service;

use Zend\View\Model\ViewModel,
    Zend\Config\Config as ZendConfig;

/**
 * Modifier
 *
 * @author hummer 
 */
class LayoutModifier
{   
    /**
     *
     * @var \Zend\View\Model\ViewModel
     */
    protected $layout;
    
    /**
     *
     * @var array
     */
    protected $blocks;
    
    /**
     *
     * @var default captureTo
     */
    protected $captureTo = 'childHtml';
    
    /**
     *
     * @var boolean
     */
    protected $isDebug = false;
    
    /**
     * 
     * @param \Zend\View\Model\ViewModel $layout
     * @param \Zend\Config\Config $blocks
     * @param string|null $layoutTemplate
     */
    public function __construct(ViewModel $layout, $blocks, $layoutTemplate = null)
    {
        $this->layout   = $layout;
        if (null !== $layoutTemplate) {
            $this->layout->setTemplate($layoutTemplate);
        }
        $this->blocks = $blocks;
    }
    
    /**
     * 
     * @param type $blocks
     * @param type $parent
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function addBlocksToLayout(\ZendConfig $blocks = null, $parent = null)
    {
        if (null === $blocks) {
            $blocks = $this->blocks;
        }
        if (null === $parent) {
            $parent = $this->layout;
        }
        foreach ($blocks as $placeholderName => $blocks) {
            foreach ($blocks as $block) {
                if ($this->isDebug) {
                    $block->instance = $this->_addDebugBlock($block->instance);
                }
                $captureTo = !is_string($placeholderName) ? $this->captureTo : $placeholderName;
                $parent->addChild($block->instance, $captureTo, true);
                if ($block->children) {
                    $this->addBlocksToLayout($block->children, $block->instance);
                }
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param \Zend\View\Model\ViewModel $viewModel
     * @return \Zend\View\Model\ViewModel
     */
    protected function _addDebugBlock(ViewModel $viewModel)
    {
        $debugBlock = new ViewModel(array(
            'blockName' => $viewModel->getVariable('nameInLayout'),
            'blockTemplate' => $viewModel->getTemplate(),
            'blockClass' => get_class($viewModel)
        ));
        $debugBlock->setTemplate('blocks/debug');
        $debugBlock->addChild($viewModel);
        return $debugBlock;
    }
    
    /**
     * 
     * @param bool $flag
     * @return \ConLayout\Service\LayoutModifier
     */
    public function setIsDebug($flag = true)
    {
        $this->isDebug = (bool) $flag;
        return $this;
    }    
}
