<?php
namespace ConLayoutTest\Service;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlocksBuilderTest extends \ConLayoutTest\AbstractTest
{    
    
    public function testFactory()
    {
        $factory = new \ConLayout\Service\BlocksBuilderFactory();
        $this->assertInstanceof(
            'ConLayout\Service\BlocksBuilder', 
            $factory->createService($this->sm)
        );
    }

    public function testCreateBlocksWithDefaultHandle()
    {        
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());
        $blocksBuilder->create();
        $this->assertEquals(1, count($blocksBuilder->getCreatedBlocks()));
        
    }

    public function testCreateBlocksWithRouteHandle()
    {
        $this->layoutService->reset()->addHandle('route');
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());
        $createdBlocks = $blocksBuilder->create(true)->getCreatedBlocks();
        $this->assertArrayHasKey('sidebar.right', $createdBlocks);
        
        $this->layoutService->addHandle('route/childroute');
        $this->layoutService->setLayoutConfig(array());
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig())->create(true);
        $this->assertArrayHasKey('sidebar', $blocksBuilder->getCreatedBlocks());
    }
    
    public function testGetBlock()
    {
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->setBlockConfig($this->layoutService->getBlockConfig());
        $this->assertInstanceOf('ConLayout\Block\Dummy', $blocksBuilder->getBlock('block.header'));
    }
}
