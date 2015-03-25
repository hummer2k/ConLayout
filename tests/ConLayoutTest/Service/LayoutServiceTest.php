<?php
namespace ConLayoutTest\Service;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutServiceTest extends \ConLayoutTest\AbstractTest
{
    public function testAddHandle()
    {
        $this->layoutService->addHandle('route');
        $this->assertEquals(array(
            'default',
            'route'
        ), $this->layoutService->getHandles());
        
        $this->layoutService->addHandle(array(
            'route/childroute',
            'controller::action'
        ));
        
        $this->assertEquals(array(
            'default', 'route', 'route/childroute', 'controller::action'
        ), $this->layoutService->getHandles());
    }
        
    public function testLayoutTemplate()
    {
        $this->layoutService->reset();
        $this->assertEquals('layout/2cols-left', $this->layoutService->getLayoutTemplate());
        
        $this->layoutService->reset();
        $this->layoutService->addHandle('route/childroute');
        $this->assertEquals('layout/2cols-right', $this->layoutService->getLayoutTemplate());
    
        $this->layoutService->reset();
        $this->layoutService->addHandle(array(
            'route/childroute',
            'controller::action'
        ));
        $this->assertEquals('layout/1col', $this->layoutService->getLayoutTemplate());
    }
    
    public function testRemoveBlocks()
    {
        $this->layoutService->reset();
        $blockConfig = $this->layoutService->getBlockConfig();
        
        $this->assertArrayHasKey('block.header', $blockConfig['header']);
        
        $this->layoutService->reset();        
        $this->layoutService->addHandle('remove-handle');
        $blockConfig = $this->layoutService->getBlockConfig();
        
        $this->assertFalse(isset($blockConfig['header']['block.header']));
        
    }
    
    public function testGetBlockConfig()
    {
        $blockConfig = $this->layoutService->getBlockConfig();
        $this->assertEquals($blockConfig, array(
            'header' => array(
                'block.header' => array(
                    'class' => 'ConLayout\Block\Dummy'
                )
            )
        ));
    }
    
    public function testGetGlobalLayoutConfig()
    {
        $globalLayoutConfig = $this->layoutService->reset()->getGlobalLayoutConfig();
        $this->assertSame($globalLayoutConfig, array(
            'default' => array(
                'layout' => 'layout/2cols-left',
                'blocks' => array(
                    'header' => array(
                        'block.header' => array(
                            'class' => 'ConLayout\Block\Dummy'
                        )
                    )
                )
            ),
            'route' => array(
                'blocks' => array(
                    'sidebar.right' => array(
                        'block.sidebar.right' => array(
                            'class' => 'ConLayout\Block\Dummy',
                            'children' => array(
                                'childCapture' => array(
                                    'block.sidebar.right.child1' => array(
                                        'class' => 'ConLayout\Block\Dummy'
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            'remove-handle' => array(
                'blocks' => array(
                    '_remove' => array(
                        'block.header' => true
                    )
                )
            ), 
            'route/childroute' => array(
                'layout' => 'layout/2cols-right',
                'blocks' => array(
                    'sidebar' => array(
                        'block.sidebar' => array(
                            'class' => 'ConLayout\Block\Dummy'
                        )
                    )
                )
            ),
            'controller::action' => array(
                'layout' => 'layout/1col'
            ),
        ));
    }
}
