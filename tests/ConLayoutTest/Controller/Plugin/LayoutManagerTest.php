<?php

namespace ConLayoutTest\Controller\Plugin;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Controller\Plugin\LayoutManager;
use ConLayout\Debug\Debugger;
use ConLayout\Handle\Handle;
use ConLayout\Layout\Layout;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayoutTest\AbstractTest;
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
            new BlockFactory(new Debugger()),
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
        $serviceManager = new \Zend\ServiceManager\ServiceManager();
        $serviceManager->setService(
            'ConLayout\Layout\LayoutInterface', $this->layout
        );
        $serviceManager->setService(
            'ConLayout\Updater\LayoutUpdaterInterface', $this->updater
        );
        $serviceManager->setService(
            'ConLayout\View\Renderer\BlockRenderer', $this->renderer
        );

        $controllerPluginManager = new \Zend\Mvc\Controller\PluginManager();
        $controllerPluginManager->setServiceLocator($serviceManager);

        $factory = new \ConLayout\Controller\Plugin\LayoutManagerFactory();
        $instance = $factory->createService($controllerPluginManager);

        $this->assertInstanceOf(
            'ConLayout\Controller\Plugin\LayoutManager',
            $instance
        );
    }

    public function testRenderBlockAndChild()
    {
        $widget = new ViewModel();
        $widget->setTemplate('widget1');

        $widgetContent = new ViewModel();
        $widgetContent->setTemplate('widget-content');

        $widget->addChild($widgetContent);

        $result = $this->layoutManager->render($widget);

        $expected = file_get_contents(__DIR__ . '/../../_files/rendered-widget.html');
        $this->assertEquals($expected, $result);
    }

    public function testRenderBlockString()
    {
        $result = $this->layoutManager->render('test-block');
        $file = __DIR__ . '/../../_files/rendered-test-block.html';
        $expected = file_get_contents($file);
        $this->assertEquals($expected, $result);        
    }

    public function testRenderNotExistingBlock()
    {
        $result = $this->layoutManager->render('___NOT_EXIST___');
        $this->assertSame('', $result);
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

    public function testRenderChildrenAppend()
    {
        $widget1 =  (new ViewModel())
            ->setTemplate('widget1');

        $widgetContent1 = (new ViewModel())
            ->setTemplate('widget-content')
            ->setAppend(true)
            ->setCaptureTo('widget1::content');

        $widgetContent2 = (new ViewModel())
            ->setTemplate('widget-content-after')
            ->setAppend(true)
            ->setOption('order', 10)
            ->setCaptureTo('widget1::content');

        $this->layout->addBlock('widget-content-1', $widgetContent1);
        $this->layout->addBlock('widget-content-2', $widgetContent2);
        $this->layout->addBlock('widget1', $widget1);

        $file = __DIR__ . '/../../_files/rendered-widget-append.html';
        
        $result = $this->layoutManager->render('widget1');

        $expected = file_get_contents($file);

        $this->assertEquals($expected, $result);

    }

    /**
     * @expectedException \Zend\View\Exception\DomainException
     */
    public function testThrowException()
    {
        $viewModel = (new ViewModel())->setTemplate('widget1');
        $childModel = (new ViewModel())
            ->setTemplate('widget-content')
            ->setTerminal(true);

        $viewModel->addChild($childModel);

        $this->layout->addBlock('widget1', $viewModel);

        $this->layoutManager->render('widget1');
    }
}
