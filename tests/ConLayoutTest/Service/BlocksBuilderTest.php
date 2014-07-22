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
        $this->layoutConfig->addHandle('route');
        $blocksBuilder = $this->getBlocksBuilder();
        $this->assertArrayHasKey('sidebar.right', $blocksBuilder->getCreatedBlocks());
        
        $this->layoutConfig->addHandle('route/childroute');
        $this->layoutConfig->setLayoutService(new \Zend\Config\Config(array(), true));
        $blocksBuilder->create(true);
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
