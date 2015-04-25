<?php

namespace ConLayoutTest\Zdt\Collector;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Debugger;
use ConLayout\Layout\Layout;
use ConLayout\Module;
use ConLayout\Service\BlocksBuilder;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Zdt\Collector\LayoutCollector;
use ConLayout\Zdt\Collector\LayoutCollectorFactory;
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
    /**
     *
     * @var LayoutCollector
     */
    protected $collector;

    public function setUp()
    {
        parent::setUp();
        $this->collector = \ConLayoutTest\Bootstrap::getServiceManager()
            ->create('ConLayout\Zdt\Collector\LayoutCollector');
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
        $event = new \Zend\Mvc\MvcEvent();
        $layoutModel = new ViewModel();
        $layoutModel->setTemplate('layout/2cols-left');
        $event->setViewModel($layoutModel);

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

        $this->assertInternalType(
            'array',
            $this->collector->getLayoutStructure()
        );

    }

}
