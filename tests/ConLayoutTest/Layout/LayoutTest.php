<?php

namespace ConLayoutTest\Layout;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Layout\Layout;
use ConLayout\Layout\LayoutFactory;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\LayoutUpdater;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Layout\Layout as TestLayout;
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
        parent::setUp();
        $this->layoutStructure = include __DIR__ . '/_files/layout-structure.php';
        $this->updaterMock = $this->getMockBuilder(
            'ConLayout\Updater\LayoutUpdaterInterface'
        )->getMock();
        $this->updaterMock->method('getLayoutStructure')
            ->willReturn(new Config($this->layoutStructure));

        $this->blockFactory = new BlockFactory();
        $this->blockFactory->setServiceLocator(new ServiceManager());
    }

    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
            'ConLayout\Block\Factory\BlockFactoryInterface',
            new BlockFactory()
        );

        $serviceManager->setService(
            'ConLayout\Updater\LayoutUpdaterInterface',
            new LayoutUpdater()
        );

        $serviceManager->setService(
            'ConLayout\Options\ModuleOptions',
            new ModuleOptions()
        );

        $factory = new LayoutFactory();
        $instance = $factory->createService($serviceManager);


        $this->assertInstanceOf('ConLayout\Layout\LayoutInterface', $instance);
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

    public function testAddBlockWithChildren()
    {
        $layout = new Layout(
            $this->blockFactory,
            new LayoutUpdater()
        );
        $block1 = new ViewModel();
        $block2 = new ViewModel([
            LayoutInterface::BLOCK_ID_VAR => 'child1'
        ]);
        $block3 = new ViewModel([
            LayoutInterface::BLOCK_ID_VAR => 'child2'
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
            $this->blockFactory,
            new LayoutUpdater()
        );
        $block1 = new ViewModel();
        $block2 = new ViewModel([
            LayoutInterface::BLOCK_ID_VAR => 'child1'
        ]);
        $block3 = new ViewModel([
            LayoutInterface::BLOCK_ID_VAR => 'child2'
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

        $layout = new TestLayout($this->blockFactory, $this->updaterMock);

        $this->assertEquals([
            'some.parent',
            'footer'
        ], $layout->getCaptureTo($viewModel));
    }

    public function testGetCaptureToDefault()
    {
        $viewModel = new ViewModel();
        $viewModel->setOption('parent', 'some.parent');

        $layout = new TestLayout($this->blockFactory, $this->updaterMock);

        $this->assertEquals([
            'some.parent',
            'content'
        ], $layout->getCaptureTo($viewModel));
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
            $this->blockFactory,
            $this->updaterMock
        );
        $layoutModel = new ViewModel();
        $layout->setRoot($layoutModel);
        $layout->load();
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
        $layout->setRoot($layoutModel);
        $layout->load();

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

    public function testIsAllowed()
    {
        $layout = new Layout(
            $this->blockFactory,
            $this->updaterMock
        );

        $layout->getEventManager()->getSharedManager()
            ->attach('ConLayout\Layout\Layout', 'isAllowed', function($e) {
                $blockId = $e->getParam('block_id');
                if ($blockId === 'widget.1') {
                    return false;
                }
                return true;
            });

        $layout->addBlock('mr.widget', new ViewModel());

        $root = new ViewModel();
        $layout->setRoot($root);
        $layout->load();

        $this->assertCount(2, $root->getChildren());

    }

    public function testAnonymousBlockId()
    {
        $viewModel = new ViewModel();
        $child = new ViewModel();
        $viewModel->addChild($child);
        $this->layout->addBlock('test.block', $viewModel);

        $this->assertEquals(
            'anonymous.content.1',
            $child->getVariable(LayoutInterface::BLOCK_ID_VAR)
        );
    }

    public function testParentBlock()
    {
        $parent = new ViewModel();
        $child  = new ViewModel();
        $child->setVariable(LayoutInterface::BLOCK_ID_VAR, 'child');

        $child2 = new ViewModel();
        $child2->setVariable(LayoutInterface::BLOCK_ID_VAR, 'child2');
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
