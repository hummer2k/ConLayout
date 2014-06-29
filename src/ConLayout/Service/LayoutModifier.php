<?php
namespace ConLayout\Service;

use Zend\View\Model\ViewModel;

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
     * @param \Zend\View\Model\ViewModel $layout
     * @param \Zend\Config\Config $blocks
     * @param string|null $layoutTemplate
     */
    public function __construct(ViewModel $layout, \Zend\Config\Config $blocks, $layoutTemplate = null)
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
    public function addBlocksToLayout($blocks = null, $parent = null)
    {
        if (null === $blocks) {
            $blocks = $this->blocks;
        }
        if (null === $parent) {
            $parent = $this->layout;
        }
        foreach ($blocks as $placeholderName => $blocks) {
            foreach ($blocks as $block) {
                $captureTo = !is_string($placeholderName) ? 'childHtml' : $placeholderName;
                $parent->addChild($block['instance'], $captureTo, true);
                if (isset($block['children'])) {
                    $this->addBlocksToLayout($block['children'], $block['instance']);
                }
            }
        }
        return $this;
    }
}
