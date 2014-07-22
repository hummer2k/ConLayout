<?php
namespace ConLayout\Service;

use Zend\View\Model\ViewModel,
    Zend\Config\Config as ZendConfig;

/**
 * Modifier
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
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
    protected $createdBlocks;
    
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
     * @param \Zend\Config\Config $createdBlocks
     */
    public function __construct(ViewModel $layout, $createdBlocks)
    {
        $this->layout   = $layout;
        $this->createdBlocks = $createdBlocks;
    }
    
    /**
     * 
     * @param type $blocks
     * @param type $parent
     * @return \ConLayout\Service\Layout\Modifier
     */
    public function addBlocksToLayout(ZendConfig $blocks = null, $parent = null)
    {
        if (null === $blocks) {
            $blocks = $this->createdBlocks;
        }
        if (null === $parent) {
            $parent = $this->layout;
        }
        foreach ($blocks as $placeholderName => $blocks) {
            foreach ($blocks as $block) {
                $blockParent = $block->instance;
                if ($this->isDebug) {                    
                    $block->instance = $this->_addDebugBlock($block->instance);                    
                }
                $captureTo = !is_string($placeholderName) ? $this->captureTo : $placeholderName;
                $parent->addChild($block->instance, $captureTo, true);
                if ($block->children) {                    
                    $this->addBlocksToLayout($block->children, $blockParent);
                }
            }
        }
        return $this;
    }
    
    /**
     * wrap ViewModel around block and set a debugger template
     * 
     * @param \Zend\View\Model\ViewModel $block
     * @return \Zend\View\Model\ViewModel
     */
    protected function _addDebugBlock(ViewModel $block)
    {
        $debugBlock = new ViewModel(array(
            'blockName' => $block->getVariable('nameInLayout'),
            'blockTemplate' => $block->getTemplate(),
            'blockClass' => get_class($block)
        ));
        $debugBlock->setTemplate('blocks/debug');
        $debugBlock->addChild($block);
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
    
    /**
     * 
     * @param string $captureTo
     */
    public function setCaptureTo($captureTo)
    {
        $this->captureTo = (string) $captureTo;
        return $this;
    }
}
