<?php
namespace ConLayoutTest\Service;

use ConLayout\Service\BlocksBuilder;
use ConLayout\Service\BlocksBuilderFactory;
use ConLayoutTest\AbstractTest;
use Zend\ServiceManager\ServiceManager;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlocksBuilderTest extends AbstractTest
{    
    
    public function testFactory()
    {
        $factory = new BlocksBuilderFactory();
        $this->assertInstanceof(
            'ConLayout\Service\BlocksBuilder', 
            $factory->createService($this->sm)
        );
    }

    public function testCreateBlocksWithDefaultHandle()
    {        
        $blocksBuilder = $this->getBlocksBuilder();
        $this->assertCount(
            1,
            $blocksBuilder->createBlocks(
                $this->layoutService->getBlockConfig()
            )
        );        
    }

    public function testCreateBlocksWithRouteHandle()
    {
        $this->layoutService->reset()->addHandle('route');
        $blocksBuilder = $this->getBlocksBuilder();
        $createdBlocks = $blocksBuilder->createBlocks(
            $this->layoutService->getBlockConfig()
        );
        $this->assertArrayHasKey('sidebar.right', $createdBlocks);
        
        $this->layoutService->addHandle('route/childroute');
        $this->layoutService->setLayoutConfig(array());
        $this->assertArrayHasKey('sidebar', $blocksBuilder->createBlocks($this->layoutService->getBlockConfig()));
    }

    public function testGetCreatedBlocks()
    {
        $blocksBuilder = $this->getBlocksBuilder();
        $blockConfig = [
            'sidebar' => [
                'widget' => [
                    'template' => 'my/template'
                ]
            ]
        ];
        $blocksBuilder->createBlocks($blockConfig);
        $this->assertInstanceOf(
            'Zend\View\Model\ViewModel',
            $blocksBuilder->getBlock('widget')
        );
    }
    
    public function testGetBlock()
    {
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->createBlocks($this->layoutService->getBlockConfig());
        $this->assertInstanceOf('ConLayout\Block\Dummy', $blocksBuilder->getBlock('block.header'));
    }

    public function testApplyActions()
    {
        $blocksBuilder = $this->getBlocksBuilder();
        $blocksBuilder->createBlocks([
            'header' => [
                'header' => [
                    'template' => 'path/to/template',
                    'actions' => [
                        'setVariable' => ['myVar', 'myVarValue']
                    ]
                ]
            ]
        ]);
        $block = $blocksBuilder->getBlock('header');
        $this->assertEquals('myVarValue', $block->getVariable('myVar'));
    }
}
