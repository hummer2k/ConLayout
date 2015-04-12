<?php

namespace ConLayoutTest\Layout;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Layout\Layout;
use ConLayout\Updater\LayoutUpdater;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutTest extends AbstractTest
{
    protected $updaterMock;

    protected $blockFactory;

    protected $layoutStructure;

    public function setUp()
    {
        $this->layoutStructure = include __DIR__ . '/_files/layout-structure.php';
        $this->updaterMock = $this->getMockBuilder(
            'ConLayout\Updater\LayoutUpdaterInterface'
        )->getMock();
        $this->updaterMock->method('getLayoutStructure')
            ->willReturn(new Config($this->layoutStructure));

        $this->blockFactory = new BlockFactory(
            $this->getMock('ConLayout\Debug\Debugger')
        );
        $this->blockFactory->setServiceLocator(new ServiceManager());
    }

    public function testAddAndGetBlock()
    {
        $layout = new Layout(
            $this->blockFactory,
            $this->updaterMock
        );
        $block = new ViewModel();
        $layout->addBlock('my-block', $block);
        $this->assertSame($block, $layout->getBlock('my-block'));
    }

    public function testAddAndGetBlockWithChild()
    {
        $layout = new Layout(
            $this->blockFactory,
            new LayoutUpdater()
        );

        $block = new ViewModel();
        $childBlock = new ViewModel();
        $block->addChild($childBlock);
        $childChildBlock = new ViewModel();
        $childBlock->addChild($childChildBlock);

        $layout->addBlock('block-with-children', $block);
        $this->assertCount(3, $layout->getBlocks());

        $testBlock = $layout->getBlock('block-with-children', true);

        $layoutModel = new ViewModel();
        $layout->injectBlocks($layoutModel);
    }

    public function testGetBlocks()
    {
        $layout = new Layout(
            $this->blockFactory,
            $this->updaterMock
        );

        $blocks = $layout->getBlocks();

        $this->assertInternalType('array', $blocks);
        $this->assertCount(3, $blocks);
    }

    public function testSortBlocks()
    {
        $updaterMock = $this->getMockBuilder('ConLayout\Updater\LayoutUpdaterInterface')
            ->getMock();
        $updaterMock
            ->method('getLayoutStructure')
            ->willReturn(new Config([]));
        $layout = new Layout(
            $this->blockFactory,
            $updaterMock
        );

        $expectedOrder = [
            1 => 'block-2',
            2 => 'block-4',
            3 => 'block-1',
            4 => 'block-3'
        ];

        $block1 = new ViewModel([], ['order' => 5]);
        $block2 = new ViewModel([], ['order' => -10]);
        $block3 = new ViewModel([], ['order' => 10]);
        $block4 = new ViewModel();

        $layout->addBlock('block-1', $block1);
        $layout->addBlock('block-2', $block2);
        $layout->addBlock('block-3', $block3);
        $layout->addBlock('block-4', $block4);

        $i = 1;
        foreach (array_keys($layout->getBlocks()) as $blockId) {
            $this->assertEquals($expectedOrder[$i], $blockId);
            $i++;
        }
    }

    public function testInjectBlocks()
    {
        $layout = new Layout(
            $this->blockFactory,
            $this->updaterMock
        );
        $layoutModel = new ViewModel();
        $layout->injectBlocks($layoutModel);
        $this->assertCount(2, $layoutModel->getChildren());
        $this->assertCount(1, $layout->getBlock('widget.1')->getChildren());
    }

    public function testRemoveBlock()
    {
        $layout = new Layout(
            $this->blockFactory,
            $this->updaterMock
        );
        $layoutModel = new ViewModel();
        $layout->removeBlock('widget.1');
        $layout->injectBlocks($layoutModel);

        $this->assertFalse($layout->getBlock('widget.1'));
        $this->assertCount(1, $layoutModel->getChildren());
    }

    public function testRemoveAddedBlock()
    {
        $layout = new Layout(
            $this->blockFactory,
            $this->updaterMock
        );
        $block = new ViewModel();
        $layout->addBlock('some-block', $block);
        $this->assertSame($block, $layout->getBlock('some-block'));

        $layout->removeBlock('some-block');

        $this->assertFalse($layout->getBlock('some-block'));
    }
}
