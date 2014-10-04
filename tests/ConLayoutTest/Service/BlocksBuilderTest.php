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
        $this->layoutService->reset()->addHandle('route');
        $blocksBuilder = $this->getBlocksBuilder();
        $createdBlocks = $blocksBuilder->create(true)->getCreatedBlocks();
        $this->assertArrayHasKey('sidebar.right', $createdBlocks);
        
        $this->layoutService->addHandle('route/childroute');
        $this->layoutService->setLayoutConfig(array());
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig())->create(true);
        $this->assertArrayHasKey('sidebar', $blocksBuilder->getCreatedBlocks());
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
