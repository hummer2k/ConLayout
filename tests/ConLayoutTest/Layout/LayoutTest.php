<?php

namespace ConLayoutTest\Layout;

use ConLayout\Block\BlockPool;
use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\Layout\Layout;
use ConLayout\Layout\LayoutFactory;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Layout\Layout as TestLayout;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\ModelInterface;

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
        parent::setUp();
        $this->layoutStructure = include __DIR__ . '/_files/layout-structure.php';
        $this->updaterMock = $this->getMockBuilder(
            LayoutUpdaterInterface::class
        )->getMock();
        $this->updaterMock->method('getLayoutStructure')
            ->willReturn(new Config($this->layoutStructure));

        $this->blockFactory = new BlockFactory([], new BlockManager(), new ServiceManager());
    }

    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
            BlockPoolInterface::class,
            new BlockPool()
        );

        $serviceManager->setService(
            LayoutUpdaterInterface::class,
            new LayoutUpdater()
        );

        $serviceManager->setService(
            ModuleOptions::class,
            new ModuleOptions()
        );

        $factory = new LayoutFactory();
        $instance = $factory->createService($serviceManager);


        $this->assertInstanceOf(LayoutInterface::class, $instance);
    }

    public function testAddAndGetBlock()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $block = new ViewModel();
        $layout->addBlock('my-block', $block);
        $this->assertSame($block, $layout->getBlock('my-block'));
    }

    public function testAddSortBlockWithBeforeAndAfter()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $block1 = new ViewModel([], ['after' => 'block2']);
        $block2 = new Viewmodel();
        $block3 = new ViewModel([], ['before' => 'block2']);
        $block4 = new ViewModel([], ['before' => 'block3']);
        $block5 = new ViewModel([], ['after' => 'block1']);

        $expectedOrder = [
            'block4',
            'block3',
            'block2',
            'block1',
            'block5'
        ];

        $layout->addBlock('block1', $block1);
        $layout->addBlock('block2', $block2);
        $layout->addBlock('block3', $block3);
        $layout->addBlock('block4', $block4);
        $layout->addBlock('block5', $block5);

        $layout->load();

        $this->assertSame($expectedOrder, array_keys($layout->getBlocks()));

    }

    public function testAddBlockWithChildren()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $block1 = new ViewModel();
        $block2 = new ViewModel([], [
            'block_id' => 'child1'
        ]);
        $block3 = new ViewModel([], [
            'block_id' => 'child2'
        ]);
        $block1->addChild($block2);
        $block1->addChild($block3);

        $layout->addBlock('my-block', $block1);

        $this->assertCount(3, $layout->getBlocks());

        $this->assertSame(
            $block2,
            $layout->getBlock('child1')
        );

        $this->assertSame(
            $block3,
            $layout->getBlock('child2')
        );

    }

    public function testAddBlockWithChildChildren()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $block1 = new ViewModel();
        $block2 = new ViewModel([], [
            'block_id' => 'child1'
        ]);
        $block3 = new ViewModel([], [
            'block_id' => 'child2'
        ]);
        $block1->addChild($block2);
        $block2->addChild($block3);

        $layout->addBlock('my-block', $block1);

        $this->assertCount(3, $layout->getBlocks());

        $this->assertSame(
            $block2,
            $layout->getBlock('child1')
        );

        $this->assertSame(
            $block3,
            $layout->getBlock('child2')
        );

    }

    public function testGetCaptureTo()
    {
        $viewModel = new ViewModel();
        $viewModel->setCaptureTo('root::footer');
        $viewModel->setOption('parent', 'some.parent');

        $layout = new TestLayout($this->updaterMock, $this->blockPool);

        $this->assertEquals([
            'some.parent',
            'footer'
        ], $layout->getCaptureTo($viewModel));
    }

    public function testGetCaptureToDefault()
    {
        $viewModel = new ViewModel();
        $viewModel->setOption('parent', 'some.parent');

        $layout = new TestLayout($this->updaterMock, $this->blockPool);

        $this->assertEquals([
            'some.parent',
            'content'
        ], $layout->getCaptureTo($viewModel));
    }

    public function testGetBlocks()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );

        $blocks = $layout->getBlocks();

        $this->assertInternalType('array', $blocks);
    }

    public function testSortBlocks()
    {
        $updaterMock = $this->getMockBuilder(LayoutUpdaterInterface::class)
            ->getMock();
        $updaterMock
            ->method('getLayoutStructure')
            ->willReturn(new Config([]));
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
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

        $layout->load();

        $i = 1;
        foreach (array_keys($layout->getBlocks()) as $blockId) {
            $this->assertEquals($expectedOrder[$i], $blockId);
            $i++;
        }
    }

    public function testInjectBlocks()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $layout->attachGenerator('blocks', $this->blocksGenerator);
        $layoutModel = new ViewModel();
        $layout->setRoot($layoutModel);
        $layout->load();

        $this->assertEquals('new/layout', $layoutModel->getTemplate());
        $this->assertCount(3, $layoutModel->getChildren());
        $this->assertCount(1, $layout->getBlock('widget.1')->getChildren());
    }

    public function testReferences()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $layout->attachGenerator('blocks', $this->blocksGenerator);
        $layoutModel = new ViewModel();
        $layout->setRoot($layoutModel);
        $layout->load();

        $this->assertEquals(
            'set/via/reference',
            $layout->getBlock('widget.1.child')->getTemplate()
        );

        $this->assertEquals(
            'set_via_reference',
            $layout->getBlock('widget.2.child.child')->getOption('some_option')
        );

    }

    public function testRemoveAddedBlock()
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool
        );
        $block = new ViewModel();
        $layout->addBlock('some-block', $block);
        $this->assertSame($block, $layout->getBlock('some-block'));

        $layout->removeBlock('some-block');

        $this->assertFalse($layout->getBlock('some-block'));
    }

    public function testAnonymousBlockId()
    {
        $viewModel = new ViewModel();
        $child = new ViewModel();
        $viewModel->addChild($child);
        $this->layout->addBlock('test.block', $viewModel);

        $this->assertEquals(
            'anonymous.content.1',
            $child->getOption('block_id')
        );
    }

    public function testParentBlock()
    {
        $parent = new ViewModel();
        $child  = new ViewModel();
        $child->setOption('block_id', 'child');

        $child2 = new ViewModel();
        $child2->setOption('block_id', 'child2');
        $child->addChild($child2);

        $parent->addChild($child);

        $this->layout->setRoot($parent);
        $this->layout->load();

        $childBlock = $this->layout->getBlock('child');

        $this->assertSame(
            $parent,
            $childBlock->getOption('parent_block')
        );

        $child2Block = $this->layout->getBlock('child2');

        $this->assertSame(
            $childBlock,
            $child2Block->getOption('parent_block')
        );

        $this->assertSame(
            $parent,
            $child2Block->getOption('parent_block')->getOption('parent_block')
        );
    }
}
