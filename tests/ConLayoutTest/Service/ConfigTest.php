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
}
