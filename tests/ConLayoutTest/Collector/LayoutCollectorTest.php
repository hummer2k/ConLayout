<?php

namespace ConLayoutTest\Collector;

use ConLayout\Collector\LayoutCollector;
use ConLayoutTest\AbstractTest;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollectorTest extends AbstractTest
{
    public function testCollect()
    {
        $layoutCollector = new LayoutCollector();

        $layoutService = $this->sm->get('ConLayout\Service\LayoutService');
        $layoutService->setLayoutConfig([
            'blocks' => [
                'sidebarLeft' => [
                    'widget1' => [
                        'template' => 'sidebar/widget'
                    ]
                ]
            ]
        ]);

        $event = $this->getMvcEvent();
        $blockConfig = $layoutService->getBlockConfig();

        $blocksBuilder = $this->sm->get('ConLayout\Service\BlocksBuilder');
        $blocksBuilder->setBlockConfig($blockConfig);

        $layoutCollector->collect($event);

        $this->assertInternalType('array', $layoutCollector->getBlocks());

        $this->assertEquals([
            'widget1' => [
                'class' => 'Zend\View\Model\ViewModel',
                'template' => 'sidebar/widget',
                'capture_to' => 'sidebarLeft'
            ]
        ], $layoutCollector->getBlocks());

        $this->assertInternalType('array', $layoutCollector->getLayoutConfig());
        $this->assertEquals(['default' => -1], $layoutCollector->getHandles());
        $this->assertEquals('1col', $layoutCollector->getLayoutTemplate());
    }

    public function testCollectDebug()
    {
        $layoutCollector = new LayoutCollector();

        $layoutService = $this->sm->get('ConLayout\Service\LayoutService');
        $layoutService->setLayoutConfig([
            'blocks' => [
                'sidebarLeft' => [
                    'widget1' => [
                        'template' => 'sidebar/widget'
                    ]
                ]
            ]
        ]);

        $event = $this->getMvcEvent();
        $blockConfig = $layoutService->getBlockConfig();
        $layoutService->addHandle('some-handle');
        $layoutService->addHandle('some/handle');

        $this->sm->get('ConLayout\Debugger')->setEnabled(true);
        $blocksBuilder = $this->sm->get('ConLayout\Service\BlocksBuilder');
        $blocksBuilder->setBlockConfig($blockConfig);

        $createdBlocks = $blocksBuilder->createBlocks();
        $layoutModifier = $this->sm->get('ConLayout\Service\LayoutModifier');
        $layoutModifier->addBlocksToLayout($createdBlocks, $event->getViewModel());

        $layoutCollector->collect($event);

        $this->assertInternalType('array', $layoutCollector->getBlocks());

        $this->assertEquals([
            'widget1' => [
                'class' => 'Zend\View\Model\ViewModel',
                'template' => 'sidebar/widget',
                'capture_to' => 'sidebarLeft'
            ]
        ], $layoutCollector->getBlocks());

        $this->assertInternalType('array', $layoutCollector->getLayoutConfig());
        $this->assertEquals([
            'default' => -1,
            'some-handle' => 0,
            'some/handle' => 1
        ], $layoutCollector->getHandles());
        $this->assertEquals('1col', $layoutCollector->getLayoutTemplate());

        $this->sm->get('ConLayout\Debugger')->setEnabled(false);
    }

    protected function getMvcEvent()
    {
        $mvcEvent = new \Zend\Mvc\MvcEvent();

        $application = $this->sm->get('Application');
        $mvcEvent->setApplication($application);

        $layout = new ViewModel();
        $layout->setTemplate('1col');

        $mvcEvent->setViewModel($layout);

        return $mvcEvent;
    }

    public function testGetName()
    {
        $layoutCollector = new LayoutCollector();
        $this->assertEquals(LayoutCollector::NAME, $layoutCollector->getName());
    }

    public function testGetPriority()
    {
        $layoutCollector = new LayoutCollector();
        $this->assertEquals(600, $layoutCollector->getPriority());
    }

    public function testGetIsDebug()
    {
        $layoutCollector = new LayoutCollector();
        $this->sm->get('ConLayout\Debugger')->setEnabled(true);
        $layoutCollector->collect($this->getMvcEvent());
        $this->assertTrue($layoutCollector->isDebug());

        $this->sm->get('ConLayout\Debugger')->setEnabled(false);
        $layoutCollector->collect($this->getMvcEvent());
        $this->assertFalse($layoutCollector->isDebug());
    }
}
