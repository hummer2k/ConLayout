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
        $this->layoutConfig->addHandle('route');
        $this->assertEquals(array(
            'default',
            'route'
        ), $this->layoutConfig->getHandles());
        
        $this->layoutConfig->addHandle(array(
            'route/childroute',
            'controller::action'
        ));
        
        $this->assertEquals(array(
            'default', 'route', 'route/childroute', 'controller::action'
        ), $this->layoutConfig->getHandles());
    }
    
    /**
     * @covers \ConLayout\Service\Config::sortBlocks
     */
    public function testSortBlocks()
    {
        $sortedBlocks = $this->layoutConfig->sortBlocks(array(
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
        $this->layoutConfig->reset();
        $this->assertEquals('layout/2cols-left', $this->layoutConfig->getLayoutTemplate());
        
        $this->layoutConfig->reset();
        $this->layoutConfig->addHandle('route/childroute');
        $this->assertEquals('layout/2cols-right', $this->layoutConfig->getLayoutTemplate());
    
        $this->layoutConfig->reset();
        $this->layoutConfig->addHandle(array(
            'route/childroute',
            'controller::action'
        ));
        $this->assertEquals('layout/1col', $this->layoutConfig->getLayoutTemplate());
    }
    
    /**
     * @covers \ConLayout\Service\Config::removeBlocks
     */
    public function testRemoveBlocks()
    {
        $this->layoutConfig->reset();
        $blockConfig = $this->layoutConfig->getBlockConfig()->toArray();
        
        $this->assertArrayHasKey('block.header', $blockConfig['header']);
        
        $this->layoutConfig->reset();        
        $this->layoutConfig->addHandle('remove-handle');
        $blockConfig = $this->layoutConfig->getBlockConfig()->toArray();
        
        $this->assertFalse(isset($blockConfig['header']['block.header']));
        
    }
    
    /**
     * @covers \ConLayout\Service\Config::getBlockConfig
     */
    public function testGetBlockConfig()
    {
        $blockConfig = $this->layoutConfig->getBlockConfig()->toArray();
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
        $globalLayoutConfig = $this->layoutConfig->reset()->getGlobalLayoutConfig();
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
