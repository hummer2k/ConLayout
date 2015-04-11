<?php

namespace ConLayoutTest\Zdt\Collector;

use ConLayout\Debugger;
use ConLayout\Module;
use ConLayout\Service\BlocksBuilder;
use ConLayout\Zdt\Collector\LayoutCollector;
use ConLayoutTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollectorTest extends AbstractTest
{
    public function testCollect()
    {
        return;
        $layoutCollector = new LayoutCollector();

        $event = $this->getMvcEvent();

        $sm = $event->getApplication()
            ->getServiceManager();
        $layoutService = $sm->get('ConLayout\Service\LayoutService');
        $layoutService->setLayoutConfig([
            'blocks' => [
                'sidebarLeft' => [
                    'widget1' => [
                        'template' => 'sidebar/widget'
                    ]
                ]
            ]
        ]);

        $blockConfig = $layoutService->getBlockConfig();

        $blocksBuilder = $sm->get('ConLayout\Service\BlocksBuilder');
        $blocksBuilder->setServiceLocator($this->sm);
        $blocksBuilder->createBlocks($blockConfig);
        
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
        return;
        $layoutCollector = new LayoutCollector();

        $event = $this->getMvcEvent();

        $sm = $event->getApplication()->getServiceManager();

        $layoutService = $sm->get('ConLayout\Service\LayoutService');
        $layoutService->setLayoutConfig([
            'blocks' => [
                'sidebarLeft' => [
                    'widget1' => [
                        'template' => 'sidebar/widget'
                    ]
                ]
            ]
        ]);

        
        $blockConfig = $layoutService->getBlockConfig();
        $layoutService->addHandle('some-handle');
        $layoutService->addHandle('some/handle');

        $sm->get('ConLayout\Debugger')->setEnabled(true);
        $blocksBuilder = $sm->get('ConLayout\Service\BlocksBuilder');
        $blocksBuilder->setServiceLocator($sm);
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

        $module = new Module();

        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $module->getConfig());

        $serviceManager->setService('ConLayout\Service\LayoutService', $this->layoutService->reset());
        $serviceManager->setService('ConLayout\Service\BlocksBuilder', new BlocksBuilder());
        $serviceManager->setService('ConLayout\Debugger', new Debugger());
        $serviceManager->setService('EventManager', new EventManager());
        $serviceManager->setService('Request', new Request());
        $serviceManager->setService('Response', new Response());

        $application = new Application([], $serviceManager);
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
        return;
        $layoutCollector = new LayoutCollector();
        $event = $this->getMvcEvent();
        $sm = $event->getApplication()->getServiceManager();
        $sm->get('ConLayout\Debugger')->setEnabled(true);
        $layoutCollector->collect($event);
        $this->assertTrue($layoutCollector->isDebug());

        $sm->get('ConLayout\Debugger')->setEnabled(false);
        $layoutCollector->collect($this->getMvcEvent());
        $this->assertFalse($layoutCollector->isDebug());
    }
}
