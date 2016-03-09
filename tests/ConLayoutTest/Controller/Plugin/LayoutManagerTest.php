<?php

namespace ConLayoutTest\Controller\Plugin;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Controller\Plugin\LayoutManager;
use ConLayout\Controller\Plugin\LayoutManagerFactory;
use ConLayout\Handle\Handle;
use ConLayout\Layout\Layout;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayoutTest\AbstractTest;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManagerTest extends AbstractTest
{
    protected $layoutManager;

    protected $updater;

    protected $layout;

    protected $renderer;

    public function setUp()
    {
        parent::setUp();
        $layout = new Layout(
            $this->layoutUpdater,
            $this->blockPool
        );

        $this->layout = $layout;

        $layout->addBlock(
            'test-block',
            (new ViewModel())->setTemplate('widget1')
        );

        $renderer = new BlockRenderer();
        $renderer->setResolver($this->getResolver());

        $this->renderer = $renderer;

        $this->layoutManager = new LayoutManager(
            $layout,
            $this->layoutUpdater,
            $renderer
        );
    }

    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            LayoutInterface::class,
            $this->layout
        );
        $serviceManager->setService(
            LayoutUpdaterInterface::class,
            $this->layoutUpdater
        );
        $serviceManager->setService(
            BlockRenderer::class,
            $this->renderer
        );

        $controllerPluginManager = new PluginManager();
        $controllerPluginManager->setServiceLocator($serviceManager);

        $factory = new LayoutManagerFactory();
        $instance = $factory->createService($controllerPluginManager);

        $this->assertInstanceOf(
            LayoutManager::class,
            $instance
        );
    }

    public function testInvoke()
    {
        $result = call_user_func($this->layoutManager);
        $this->assertSame($result, $this->layoutManager);
    }

    public function testGetBlock()
    {
        $block = $this->layoutManager->getBlock('test-block');
        $this->assertInstanceOf(ModelInterface::class, $block);

        $this->assertFalse($this->layoutManager->getBlock('___NOT_EXISTS___'));
    }

    public function testAddHandle()
    {
        $this->layoutManager->addHandle(new Handle('my-test-handle', 1));

        $this->assertSame([
            'default',
            'my-test-handle'
        ], $this->layoutUpdater->getHandles());

        $this->layoutManager->addHandle(new Handle('test-handle-2', -10));

        $this->assertSame([
            'test-handle-2',
            'default',
            'my-test-handle'
        ], $this->layoutUpdater->getHandles());
    }

    public function testRemoveHandle()
    {
        $this->layoutManager->addHandle(new Handle('my-test-handle', 5));

        $this->assertSame([
            'default',
            'my-test-handle'
        ], $this->layoutUpdater->getHandles());

        $this->layoutManager->removeHandle('my-test-handle');

        $this->assertSame([
            'default'
        ], $this->layoutUpdater->getHandles());
    }

    public function testRemoveBlock()
    {
        $testBlock = $this->layoutManager->getBlock('test-block');
        $this->assertInstanceOf(ModelInterface::class, $testBlock);
        $this->layoutManager->removeBlock('test-block');
        $this->assertFalse($this->layoutManager->getBlock('test-block'));
    }

    public function testAddBlock()
    {
        $block = new ViewModel();
        $this->layoutManager->addBlock('some-block', $block);
        $this->assertSame($block, $this->layoutManager->getBlock('some-block'));
        $this->assertSame($block, $this->layout->getBlock('some-block'));
    }

    public function testGetBlocks()
    {
        $this->assertInternalType('array', $this->layoutManager->getBlocks());
        $this->layoutManager->addBlock('test-block', new ViewModel());
        $this->assertCount(1, $this->layoutManager->getBlocks());
    }

    public function testSetRoot()
    {
        $root = new ViewModel();
        $this->layoutManager->setRoot($root);

        $this->assertSame(
            $root,
            $this->layout->getBlock(LayoutInterface::BLOCK_ID_ROOT)
        );
    }

    public function testLoad()
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

    public function testSetHandlesScalar()
    {
        $this->layoutManager->setHandles([
            'my-handle-1' => 5,
            'my-handle-2' => 3,
            'my-handle-3' => 6
        ]);

        $this->assertSame([
            'my-handle-2',
            'my-handle-1',
            'my-handle-3'
        ], $this->layoutUpdater->getHandles());
    }

    public function testSetHandlesObject()
    {
        $this->layoutManager->setHandles([
            new Handle('my-handle-1', 5),
            new Handle('my-handle-2', 3),
            new Handle('my-handle-3', 6)
        ]);

        $this->assertSame([
            'my-handle-2',
            'my-handle-1',
            'my-handle-3'
        ], $this->layoutUpdater->getHandles());
    }
}
