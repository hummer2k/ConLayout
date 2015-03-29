<?php

namespace ConLayoutTest\Controller\Plugin;

use ConLayout\Controller\Plugin\BlockManagerFactory;
use ConLayoutTest\AbstractTest;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockManagerTest extends AbstractTest
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
        $instance = $this->getBlockManager();
        $this->assertInstanceOf('ConLayout\Controller\Plugin\BlockManager', $instance);
    }
    
    protected function getBlockManager()
    {
        $factory = new BlockManagerFactory();
        $instance = $factory->createService($this->sm->get('ControllerPluginManager'));
        return $instance;
    }

    public function testInvoke()
    {
        $blockManager = $this->getBlockManager();
        $result = $blockManager();
        $this->assertSame($blockManager, $result);
        
        $this->assertInstanceOf(
            'Zend\View\Model\ViewModel',
            $blockManager('my-widget')
        );

        $this->assertInstanceOf(
            'Zend\View\Model\ViewModel', 
            $blockManager->getBlock('my-widget')
        );

        $this->assertInstanceOf(
            'ConLayout\Block\Dummy',
            $blockManager->getBlock('header')
        );
        
    }

    public function testRender()
    {
        $blockManager = $this->getBlockManager();

        $result = $blockManager->render('my-widget');

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

        $result = $blockManager->render($viewModel);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../_files/render-test.html'),
            $result
        );

    }

    public function testGetter()
    {
        $blockManager = $this->getBlockManager();

        $this->assertInstanceOf(
            'ConLayout\Service\LayoutService',
            $blockManager->getLayoutService()
        );
    }

    public function testAddHandle()
    {
        $blockManager = $this->getBlockManager();
        $layoutService = $blockManager->getLayoutService();
        $layoutService->reset();
        $blockManager->addHandle('my-handle');

        $this->assertEquals([
            'default',
            'my-handle'
        ], $layoutService->getHandles());

    }

    public function testSetHandles()
    {
        $blockManager = $this->getBlockManager();
        $layoutService = $blockManager->getLayoutService();
        $blockManager->setHandles(['my-handle']);

        $this->assertEquals([
            'my-handle'
        ], $layoutService->getHandles());

    }

    public function testRemoveHandle()
    {
        $blockManager = $this->getBlockManager();
        $layoutService = $blockManager->getLayoutService();
        $layoutService->reset();
        $layoutService->addHandle('my-handle');
        
        $blockManager->removeHandle('my-handle');

        $this->assertEquals([
            'default'
        ], $layoutService->getHandles());

    }
}