<?php
namespace ConLayoutTest\Service;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ConfigTest extends \ConLayoutTest\AbstractTest
{
    /**
     * @covers \ConLayout\Service\Config::addHandle
     */
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
    
    /**
     * @covers \ConLayout\Service\Config::sortBlocks
     */
    public function testSortBlocks()
    {
        $sortedBlocks = $this->layoutService->sortBlocks(array(
            'sidebar' => array(
                'block.sidebar_1' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => 10
                ),
                'block.sidebar_2' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => 5
                ),
                'block.sidebar_3' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => 20
                ),
                'block.sidebar_4' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => -20
                )
            )
        ));
        $this->assertEquals($sortedBlocks, array(
            'sidebar' => array(
                'block.sidebar_4' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => -20
                ),
                'block.sidebar_2' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => 5
                ),
                'block.sidebar_1' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => 10
                ),
                'block.sidebar_3' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'order' => 20
                )
            )
        ));
    }
    
    /**
     * @covers \ConLayout\Service\Config::getLayoutTemplate
     * @covers \ConLayout\Service\Config\Sorter::sort
     */
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
    
    /**
     * @covers \ConLayout\Service\Config::removeBlocks
     */
    public function testRemoveBlocks()
    {
        $this->layoutService->reset();
        $blockConfig = $this->layoutService->getBlockConfig()->toArray();
        
        $this->assertArrayHasKey('block.header', $blockConfig['header']);
        
        $this->layoutService->reset();        
        $this->layoutService->addHandle('remove-handle');
        $blockConfig = $this->layoutService->getBlockConfig()->toArray();
        
        $this->assertFalse(isset($blockConfig['header']['block.header']));
        
    }
    
    /**
     * @covers \ConLayout\Service\Config::getBlockConfig
     */
    public function testGetBlockConfig()
    {
        $blockConfig = $this->layoutService->getBlockConfig()->toArray();
        $this->assertEquals($blockConfig, array(
            'header' => array(
                'block.header' => array(
                    'class' => 'ConLayout\Block\Dummy'
                )
            )
        ));
    }
    
    /**
     * @covers \ConLayout\Service\Config::getGlobalLayoutConfig
     */
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
