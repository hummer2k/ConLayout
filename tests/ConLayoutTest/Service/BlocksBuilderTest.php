<?php
namespace ConLayoutTest\Service;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlocksBuilderTest extends \ConLayoutTest\AbstractTest
{    
    /**
     * @covers \ConLayout\Service\BlocksBuilder::getCreatedBlocks
     */
    public function testCreateBlocksWithDefaultHandle()
    {        
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->create();
        $this->assertEquals(1, count($blocksBuilder->getCreatedBlocks()));
        
    }
    /**
     * @covers \ConLayout\Service\BlocksBuilder::getCreatedBlocks
     */
    public function testCreateBlocksWithRouteHandle()
    {
        $this->layoutService->addHandle('route');
        $blocksBuilder = $this->getBlocksBuilder();
        $createdBlocks = $blocksBuilder->getCreatedBlocks();
        $this->assertNotEmpty($createdBlocks->get('sidebar.right'));
        
        $this->layoutService->addHandle('route/childroute');
        $this->layoutService->setLayoutConfig(new \Zend\Config\Config(array(), true));
        $blocksBuilder->create(true);
        $this->assertNotEmpty($blocksBuilder->getCreatedBlocks()->get('sidebar'));
    }
    
    /**
     * @covers \ConLayout\Service\BlocksBuilder::getBlock
     */
    public function testGetBlock()
    {
        $blocksBuilder = $this->getBlocksBuilder();
        $this->assertInstanceOf('ConLayout\Block\Dummy', $blocksBuilder->getBlock('block.header'));
    }
}
