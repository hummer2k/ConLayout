<?php

declare(strict_types=1);

namespace ConLayoutTest;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Handle\Handle;
use ConLayout\Layout\LayoutInterface;
use ConLayout\LayoutManager;
use ConLayout\LayoutManagerFactory;
use ConLayout\Updater\LayoutUpdaterInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

class LayoutManagerTest extends AbstractTestCase
{
    protected LayoutManager $layoutManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockPool->add(
            'test-block',
            (new ViewModel())->setTemplate('widget1'),
        );

        $this->layoutManager = new LayoutManager(
            $this->layout,
            $this->layoutUpdater,
            $this->blockPool,
        );
    }

    public function testFactory(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            LayoutInterface::class,
            $this->layout,
        );
        $serviceManager->setService(
            LayoutUpdaterInterface::class,
            $this->layoutUpdater,
        );
        $serviceManager->setService(
            BlockPoolInterface::class,
            $this->blockPool,
        );

        $factory = new LayoutManagerFactory();
        $instance = $factory($serviceManager, LayoutManager::class);

        $this->assertInstanceOf(
            LayoutManager::class,
            $instance,
        );
    }

    public function testInvoke(): void
    {
        $result = call_user_func($this->layoutManager);
        $this->assertSame($result, $this->layoutManager);
    }

    public function testGetBlock(): void
    {
        $block = $this->layoutManager->getBlock('test-block');
        $this->assertInstanceOf(ModelInterface::class, $block);

        $this->assertFalse($this->layoutManager->getBlock('___NOT_EXISTS___'));
    }

    public function testAddHandle(): void
    {
        $this->layoutManager->addHandle(new Handle('my-test-handle', 1));

        $this->assertSame([
            'default',
            'my-test-handle',
        ], $this->layoutUpdater->getHandles());

        $this->layoutManager->addHandle(new Handle('test-handle-2', -10));

        $this->assertSame([
            'test-handle-2',
            'default',
            'my-test-handle',
        ], $this->layoutUpdater->getHandles());
    }

    public function testRemoveHandle(): void
    {
        $this->layoutManager->addHandle(new Handle('my-test-handle', 5));

        $this->assertSame([
            'default',
            'my-test-handle',
        ], $this->layoutUpdater->getHandles());

        $this->layoutManager->removeHandle('my-test-handle');

        $this->assertSame([
            'default',
        ], $this->layoutUpdater->getHandles());
    }

    public function testRemoveBlock(): void
    {
        $testBlock = $this->layoutManager->getBlock('test-block');
        $this->assertInstanceOf(ModelInterface::class, $testBlock);
        $this->layoutManager->removeBlock('test-block');
        $this->assertFalse($this->layoutManager->getBlock('test-block'));
    }

    public function testAddBlock(): void
    {
        $block = new ViewModel();
        $this->layoutManager->addBlock('some-block', $block);
        $this->assertSame($block, $this->layoutManager->getBlock('some-block'));
        $this->assertSame($block, $this->layout->getBlock('some-block'));
    }

    public function testGetBlocks(): void
    {
        $this->assertIsArray($this->layoutManager->getBlocks());
        $this->layoutManager->addBlock('test-block', new ViewModel());
        $this->assertCount(1, $this->layoutManager->getBlocks());
    }

    public function testSetRoot(): void
    {
        $root = new ViewModel();
        $this->layoutManager->setRoot($root);

        $this->assertSame(
            $root,
            $this->layout->getBlock(LayoutInterface::BLOCK_ID_ROOT),
        );
    }

    public function testLoad(): void
    {
        $root = new ViewModel();
        $content = new ViewModel();
        $someWidget = new ViewModel();

        $this->layoutManager->addBlock('root::content', $content);
        $this->layoutManager->addBlock('root::sidebarLeft', $someWidget);
        $this->layout->setRoot($root);

        $this->layoutManager->load();

        // layout already has a block added via setUp()
        $this->assertCount(3, $root->getChildren());
    }

    public function testSetHandlesScalar(): void
    {
        $this->layoutManager->setHandles([
            'my-handle-1' => 5,
            'my-handle-2' => 3,
            'my-handle-3' => 6,
        ]);

        $this->assertSame([
            'my-handle-2',
            'my-handle-1',
            'my-handle-3',
        ], $this->layoutUpdater->getHandles());
    }

    public function testSetHandlesObject(): void
    {
        $this->layoutManager->setHandles([
            new Handle('my-handle-1', 5),
            new Handle('my-handle-2', 3),
            new Handle('my-handle-3', 6),
        ]);

        $this->assertSame([
            'my-handle-2',
            'my-handle-1',
            'my-handle-3',
        ], $this->layoutUpdater->getHandles());
    }
}
