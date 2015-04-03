<?php

namespace ConLayoutTest\Controller\Plugin;

use ConLayout\Controller\Plugin\LayoutManagerFactory;
use ConLayoutTest\AbstractTest;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutManagerTest extends AbstractTest
{
    public function setUp()
    {
        parent::setUp();
        $blocksBuilder = $this->sm->get('ConLayout\Service\BlocksBuilder');
        $blocksBuilder->createBlocks([
            'sidebar' => [
                'my-widget' => [
                    'template' => 'blocks/render-test',
                    'vars' => [
                        'title' => 'Lorem Ipsum',
                        'content' => 'Dolor sit amet.'
                    ]
                ]
            ],
            'header' => [
                'header' => [
                    'class' => 'ConLayout\Block\Dummy'
                ]
            ]
        ]);
    }

    public function testFactory()
    {
        $instance = $this->getLayoutManager();
        $this->assertInstanceOf('ConLayout\Controller\Plugin\LayoutManager', $instance);
    }
    
    protected function getLayoutManager()
    {
        $factory = new LayoutManagerFactory();
        $instance = $factory->createService($this->sm->get('ControllerPluginManager'));
        return $instance;
    }

    public function testInvoke()
    {
        $layoutManager = $this->getLayoutManager();
        $result = $layoutManager();
        $this->assertSame($layoutManager, $result);
        
        $this->assertInstanceOf(
            'Zend\View\Model\ViewModel',
            $layoutManager('my-widget')
        );

        $this->assertInstanceOf(
            'Zend\View\Model\ViewModel', 
            $layoutManager->getBlock('my-widget')
        );

        $this->assertInstanceOf(
            'ConLayout\Block\Dummy',
            $layoutManager->getBlock('header')
        );
        
    }

    public function testRender()
    {
        $layoutManager = $this->getLayoutManager();

        $result = $layoutManager->render('my-widget');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../_files/render-test.html'),
            $result
        );
        

        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTemplate('blocks/render-test');
        $viewModel->setVariables([
            'title' => 'Lorem Ipsum',
            'content' => 'Dolor sit amet.'
        ]);

        $result = $layoutManager->render($viewModel);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../_files/render-test.html'),
            $result
        );

    }

    public function testGetter()
    {
        $layoutManager = $this->getLayoutManager();

        $this->assertInstanceOf(
            'ConLayout\Service\LayoutService',
            $layoutManager->getLayoutService()
        );
    }

    public function testAddHandle()
    {
        $layoutManager = $this->getLayoutManager();
        $layoutService = $layoutManager->getLayoutService();
        $layoutService->reset();
        $layoutManager->addHandle('my-handle');

        $this->assertEquals([
            'default',
            'my-handle'
        ], $layoutService->getHandles());

    }

    public function testSetHandles()
    {
        $layoutManager = $this->getLayoutManager();
        $layoutService = $layoutManager->getLayoutService();
        $layoutManager->setHandles(['my-handle']);

        $this->assertEquals([
            'my-handle'
        ], $layoutService->getHandles());

    }

    public function testRemoveHandle()
    {
        $layoutManager = $this->getLayoutManager();
        $layoutService = $layoutManager->getLayoutService();
        $layoutService->reset();
        $layoutService->addHandle('my-handle');
        
        $layoutManager->removeHandle('my-handle');

        $this->assertEquals([
            'default'
        ], $layoutService->getHandles());

    }
}