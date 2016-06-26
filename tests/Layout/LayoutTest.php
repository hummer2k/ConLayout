<?php

namespace ConLayoutTest\Layout;

use ConLayout\Block\BlockPool;
use ConLayout\Block\BlockPoolInterface;
use ConLayout\Block\Factory\BlockFactory;
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

        $this->blockFactory = new BlockFactory([], new BlockManager($this->sm), new ServiceManager());
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
        $instance = $factory($serviceManager, LayoutInterface::class);


        $this->assertInstanceOf(LayoutInterface::class, $instance);
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

    public function testParentBlockIsAddedAsOption()
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
