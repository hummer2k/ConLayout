<?php
namespace ConLayoutTest\Controller\Plugin;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManagerTest
    extends \ConLayoutTest\AbstractTest
{
    /**
     * @covers \ConLayout\Controller\Plugin\BlockManager::getBlock
     */
    public function testGetBlock()
    {
        $this->layoutConfig->reset();
        $blockManager = $this->getBlockManager();
        $this->assertInstanceOf('ConLayout\Block\Dummy', $blockManager->getBlock('block.header'));
    }
    
    /**
     * @covers \ConLayout\Controller\Plugin\BlockManager::getBlocks
     */
    public function testGetBlocks()
    {
        $blocks = $this->getBlockManager()->getBlocks();
        $this->assertInternalType('array', $blocks);
    }
        
    protected function getBlockManager()
    {
        $blockManager = new \ConLayout\Controller\Plugin\BlockManager(
            $this->getBlocksBuilder(),
            $this->layoutConfig,
            $this->getMock('Zend\View\Renderer\PhpRenderer')
        );
        return $blockManager;
    }
}
