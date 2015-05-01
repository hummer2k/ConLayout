<?php

namespace ConLayoutTest\Controller\Plugin;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Controller\Plugin\LayoutManager;
use ConLayout\Controller\Plugin\LayoutManagerFactory;
use ConLayout\Handle\Handle;
use ConLayout\Layout\Layout;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayoutTest\AbstractTest;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
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
        $updater = new LayoutUpdater();
        $this->updater = $updater;
        $layout = new Layout(
            new BlockFactory(),
            $updater
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
            $updater,
            $renderer
        );
    }

    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'ConLayout\Layout\LayoutInterface', $this->layout
        );
        $serviceManager->setService(
            'ConLayout\Updater\LayoutUpdaterInterface', $this->updater
        );
        $serviceManager->setService(
            'ConLayout\View\Renderer\BlockRenderer', $this->renderer
        );

        $controllerPluginManager = new PluginManager();
        $controllerPluginManager->setServiceLocator($serviceManager);

        $factory = new LayoutManagerFactory();
        $instance = $factory->createService($controllerPluginManager);

        $this->assertInstanceOf(
            'ConLayout\Controller\Plugin\LayoutManager',
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
        $this->assertInstanceOf('Zend\View\Model\ModelInterface', $block);

        $this->assertFalse($this->layoutManager->getBlock('___NOT_EXISTS___'));
    }

    public function testAddHandle()
    {
        $this->layoutManager->addHandle('my-test-handle');

        $this->assertSame([
            'default',
            'my-test-handle'
        ], $this->updater->getHandles());

        $this->layoutManager->addHandle(new Handle('test-handle-2', -10));

        $this->assertSame([
            'test-handle-2',
            'default',
            'my-test-handle'
        ], $this->updater->getHandles());
    }

    public function testRemoveHandle()
    {
        $this->layoutManager->addHandle('my-test-handle', 5);

        $this->assertSame([
            'default',
            'my-test-handle'
        ], $this->updater->getHandles());

        $this->layoutManager->removeHandle('my-test-handle');

        $this->assertSame([
            'default'
        ], $this->updater->getHandles());
    }

    public function testRemoveBlock()
    {
        $testBlock = $this->layoutManager->getBlock('test-block');
        $this->assertInstanceOf('Zend\View\Model\ModelInterface', $testBlock);
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
}
