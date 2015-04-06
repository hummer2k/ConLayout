<?php

namespace ConLayoutTest\Service;

use ConLayout\Factory\BlocksFactory;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlocksFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateBlocks()
    {
        $blocksFactory = new BlocksFactory();
        $blockConfig = [
            'header' => [
                'capture_to' => 'header'
            ],
            'widget.1' => [
                'capture_to' => 'sidebarLeft'
            ],
            'widget.2' => [
                'capture_to' => 'sidebarRight'
            ],
            'widget.2.child' => [
                'capture_to' => 'widget.2::childHtml'
            ],
            'widget.2.child.child' => [
                'capture_to' => 'widget.2.child::content',
                'options' => [
                    'order' => 100
                ]
            ],
            'widget.3.child.child' => [
                'capture_to' => 'widget.2.child::content',
                'options' => [
                    'order' => -50
                ]
            ],
            'footer' => [
                'capture_to' => 'footer'
            ]
        ];

        $layout = new ViewModel();
        $layout->setTemplate('layout/2cols-left');

        $blocksFactory->createBlocks($blockConfig);
        $blocksFactory->injectBlocks($layout);
        
        $layout = $blocksFactory->getBlock('layout');

        $this->assertCount(4, $layout->getChildren());
        
        $widget2 = $blocksFactory->getBlock('widget.2');
        $this->assertCount(1, $widget2->getChildren());

        $widget1 = $blocksFactory->getBlock('widget.1');
        $this->assertCount(0, $widget1->getChildren());

        $widget2Child = $blocksFactory->getBlock('widget.2.child');
        $this->assertCount(2, $widget2Child->getChildren());
    }

    public function testRemoveBlock()
    {
        $blocksFactory = new BlocksFactory();
        $blockConfig = [
            'header' => [
                'capture_to' => 'header'
            ],
            'widget.1' => [
                'capture_to' => 'sidebarLeft'
            ],
            'widget.2' => [
                'capture_to' => 'sidebarRight'
            ],
            'widget.2.child' => [
                'capture_to' => 'widget.2::childHtml'
            ],
            'widget.2.child.child' => [
                'capture_to' => 'widget.2.child::content',
                'options' => [
                    'order' => 100
                ]
            ],
            'widget.3.child.child' => [
                'capture_to' => 'widget.2.child::content',
                'options' => [
                    'order' => -50
                ]
            ],
            'footer' => [
                'capture_to' => 'footer'
            ]
        ];

        $layout = new ViewModel();
        $layout->setTemplate('layout/2cols-left');

        $blocksFactory->createBlocks($blockConfig);

        $blocksFactory->removeBlock('footer');

        $this->assertFalse($blocksFactory->getBlock('footer'));
    }

    public function testSortOrder()
    {
        $blocksFactory = new BlocksFactory();
        $blockConfig = [
            'my.block.2' => [
                'capture_to' => 'sidebarLeft',
                'options' => [
                    'order' => 100
                ]
            ],
            'my.block.4' => [
                'capture_to' => 'sidebarLeft',
                'options' => [
                    'order' => 2000
                ]
            ],
            'my.block.3' => [
                'capture_to' => 'sidebarLeft',
                'options' => [
                    'order' => 1000
                ]
            ],
            'my.block' => [
                'capture_to' => 'sidebarLeft',
                'options' => [
                    'order' => -100
                ]
            ]            
        ];

        $blocksFactory->createBlocks($blockConfig);
        $blocksFactory->sortBlocks();

        $positions = [
            1 => 'my.block',
            2 => 'my.block.2',
            3 => 'my.block.3',
            4 => 'my.block.4'
        ];

        $i = 1;
        foreach (array_keys($blocksFactory->getBlocks()) as $blockName) {
            $this->assertEquals($positions[$i], $blockName);
            $i++;
        }
    }
}