<?php
namespace ConLayoutTest\Service;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierTest extends \ConLayoutTest\AbstractTest
{
    /**
     * @covers \ConLayout\Service\LayoutModifier::addBlocksToLayout
     */
    public function testAddBlocksToLayout()
    {
        $this->layoutConfig->reset();
        $layout = new \Zend\View\Model\ViewModel();
        $layoutModifier = new \ConLayout\Service\LayoutModifier(
            $layout,
            $this->getBlocksBuilder()->getCreatedBlocks(),
            $this->layoutConfig->getLayoutTemplate()
        );        
        $layoutModifier->addBlocksToLayout();
        $this->assertEquals(
            $layout->getTemplate(), 
            $this->layoutConfig->getLayoutTemplate()
        );        
        
        $this->assertEquals(1, $layout->count());
        
        $this->layoutConfig->reset();
        $this->layoutConfig->addHandle(array('route', 'route/childroute'));        
        $layout = new \Zend\View\Model\ViewModel();
        
        $layoutModifier = new \ConLayout\Service\LayoutModifier(
            $layout,
            $this->getBlocksBuilder()->getCreatedBlocks(),
            $this->layoutConfig->getLayoutTemplate()
        );
        $layoutModifier->addBlocksToLayout();
        $this->assertEquals(3, $layout->count());
    }
}
