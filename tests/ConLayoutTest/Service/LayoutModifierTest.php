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
        $this->layoutService->reset();
        $layout = new \Zend\View\Model\ViewModel();
        $layoutModifier = new \ConLayout\Service\LayoutModifier(
            $layout,
            $this->getBlocksBuilder()->getCreatedBlocks()
        );
        $layoutModifier->addBlocksToLayout(); 
        
        $this->assertEquals(1, $layout->count());
        
        $this->layoutService->reset();
        $this->layoutService->addHandle(array('route', 'route/childroute'));        
        $layout = new \Zend\View\Model\ViewModel();
        
        $layoutModifier = new \ConLayout\Service\LayoutModifier(
            $layout,
            $this->getBlocksBuilder()->getCreatedBlocks()
        );
        $layoutModifier->addBlocksToLayout();
        $this->assertEquals(3, $layout->count());
    }
}
