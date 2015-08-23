<?php

namespace ConLayoutTest\Zdt\Collector;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Layout\Layout;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Zdt\Collector\LayoutCollector;
use ConLayout\Zdt\Collector\LayoutCollectorFactory;
use ConLayoutTest\AbstractTest;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollectorTest extends AbstractTest
{
    /**
     *
     * @var LayoutCollector
     */
    protected $collector;

    public function setUp()
    {
        parent::setUp();
        $this->collector = new LayoutCollector(
            $this->layout,
            $this->layoutUpdater
        );
    }

    public function testFactory()
    {
        $layoutCollectorFactory = new LayoutCollectorFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'ConLayout\Updater\LayoutUpdaterInterface',
            new LayoutUpdater()
        );
        $serviceManager->setService(
            'ConLayout\Layout\LayoutInterface',
            new Layout(
                new BlockFactory(),
                new LayoutUpdater()
            )
        );

        $instance = $layoutCollectorFactory->createService($serviceManager);
        $this->assertInstanceOf(
            'ConLayout\Zdt\Collector\LayoutCollector',
            $instance
        );
    }

    public function testGetName()
    {
        $this->assertEquals(
            LayoutCollector::NAME,
            $this->collector->getName()
        );
    }

    public function testGetPriority()
    {
        $this->assertEquals(600, $this->collector->getPriority());
    }

    public function testCollect()
    {
        $event = new MvcEvent();
        $layoutModel = new ViewModel();
        $layoutModel->setTemplate('layout/2cols-left');
        $event->setViewModel($layoutModel);

        $testBlock = new ViewModel();
        $testBlock->setTemplate('test/block');
        $testBlock->setCaptureTo('sidebarLeft');

        $this->layout->addBlock('test.block', $testBlock);

        $this->collector->collect($event);

        $this->assertEquals(
            'layout/2cols-left',
            $this->collector->getLayoutTemplate()
        );

        $this->assertInternalType(
            'array',
            $this->collector->getHandles()
        );

        $this->assertContainsOnlyInstancesOf(
            'ConLayout\Handle\HandleInterface',
            $this->collector->getHandles()
        );

        $this->assertInternalType(
            'array',
            $this->collector->getBlocks()
        );

        $testBlockArray = current($this->collector->getBlocks());

        $this->assertEquals(
            'test/block',
            $testBlockArray['template']
        );

        $this->assertEquals(
            'sidebarLeft',
            $testBlockArray['capture_to']
        );

        $this->assertEquals(
            'Zend\View\Model\ViewModel',
            $testBlockArray['class']
        );

        $this->assertInternalType(
            'array',
            $this->collector->getLayoutStructure()
        );

    }

}
