<?php

declare(strict_types=1);

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
use ConLayoutTest\AbstractTestCase;
use ConLayoutTest\Layout\Layout as TestLayout;
use Laminas\Config\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;

class LayoutTest extends AbstractTestCase
{
    protected $updaterMock;

    protected $layoutStructure;

    protected function setUp(): void
    {
        parent::setUp();
        $this->layoutStructure = include __DIR__ . '/_files/layout-structure.php';
        $this->updaterMock = $this->getMockBuilder(
            LayoutUpdaterInterface::class,
        )->getMock();
        $this->updaterMock->method('getLayoutStructure')
            ->willReturn(new Config($this->layoutStructure));

        $this->blockFactory = new BlockFactory([], new BlockManager($this->sm));
    }

    public function testFactory(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
            BlockPoolInterface::class,
            new BlockPool(),
        );

        $serviceManager->setService(
            LayoutUpdaterInterface::class,
            new LayoutUpdater(),
        );

        $serviceManager->setService(
            ModuleOptions::class,
            new ModuleOptions(),
        );

        $factory = new LayoutFactory();
        $instance = $factory($serviceManager, LayoutInterface::class);


        $this->assertInstanceOf(LayoutInterface::class, $instance);
    }

    public function testGetCaptureTo(): void
    {
        $viewModel = new ViewModel();
        $viewModel->setCaptureTo('root::footer');
        $viewModel->setVariable('parent', 'some.parent');

        $layout = new TestLayout($this->updaterMock, $this->blockPool);

        $this->assertEquals([
            'some.parent',
            'footer',
        ], $layout->getCaptureTo($viewModel));
    }

    public function testGetCaptureToDefault(): void
    {
        $viewModel = new ViewModel();
        $viewModel->setVariable('parent', 'some.parent');

        $layout = new TestLayout($this->updaterMock, $this->blockPool);

        $this->assertEquals([
            'some.parent',
            'content',
        ], $layout->getCaptureTo($viewModel));
    }

    public function testInjectBlocks(): void
    {
        $layout = new Layout(
            $this->updaterMock,
            $this->blockPool,
        );
        $layout->attachGenerator('blocks', $this->blocksGenerator);
        $layoutModel = new ViewModel();
        $layout->setRoot($layoutModel);
        $layout->load();

        $this->assertEquals('new/layout', $layoutModel->getTemplate());
        $this->assertCount(3, $layoutModel->getChildren());
        $this->assertCount(1, $layout->getBlock('widget.1')->getChildren());
    }

    public function testParentBlockIsAddedAsMetadata(): void
    {
        $parent = new ViewModel();
        $child  = new ViewModel();
        $child->setVariable('block_id', 'child');

        $child2 = new ViewModel();
        $child2->setVariable('block_id', 'child2');
        $child->addChild($child2);

        $parent->addChild($child);

        $this->layout->setRoot($parent);
        $this->layout->load();

        $childBlock = $this->layout->getBlock('child');

        $this->assertSame(
            $parent,
            $childBlock->getVariable('parent_block'),
        );

        $child2Block = $this->layout->getBlock('child2');

        $this->assertSame(
            $childBlock,
            $child2Block->getVariable('parent_block'),
        );

        $this->assertSame(
            $parent,
            $child2Block->getVariable('parent_block')->getVariable('parent_block'),
        );
    }
}
