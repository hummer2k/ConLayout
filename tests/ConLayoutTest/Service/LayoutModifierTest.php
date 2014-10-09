<?php
namespace ConLayoutTest\Service;

use ConLayout\Service\LayoutModifier,
    ConLayoutTest\AbstractTest,
    Zend\View\Model\ViewModel;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierTest extends AbstractTest
{
    /**
     * @covers \ConLayout\Service\LayoutModifier::addBlocksToLayout
     */
    public function testAddBlocksToLayout()
    {
        $this->layoutService->reset();
        $layout = new ViewModel();
        $layoutModifier = new LayoutModifier();
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());       
        $createdBlocks = $blocksBuilder->getCreatedBlocks();
        
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        ); 
        
        $this->assertEquals(1, $layout->count());
    }
    
    public function testAddBlocksToLayoutRouteHandle()
    {
        $this->layoutService->reset();
        $this->layoutService->addHandle(array('route', 'route/childroute'));        
        $layout = new ViewModel();
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());       
        $createdBlocks = $blocksBuilder->getCreatedBlocks();
        
        $layoutModifier = new LayoutModifier();
        $layoutModifier->addBlocksToLayout(
            $createdBlocks,
            $layout
        );
        $this->assertEquals(3, $layout->count());
    }
}
