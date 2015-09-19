<?php

namespace ConLayoutTest\Zdt\Collector;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Layout\Layout;
use ConLayout\Layout\LayoutInterface;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayout\Zdt\Collector\LayoutCollector;
use ConLayout\Zdt\Collector\LayoutCollectorFactory;
use ConLayoutTest\AbstractTest;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\AggregateResolver;

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
        $resolver = new AggregateResolver();
        $resolver->attach($this->getResolver());
        $this->collector = new LayoutCollector(
            $this->layout,
            $this->layoutUpdater,
            $resolver
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
        $serviceManager->setService(
            'ViewResolver',
            new AggregateResolver()
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
        $testBlock->setTemplate('widget1');
        $testBlock->setCaptureTo('sidebarLeft');
        $testBlock->setVariable(LayoutInterface::BLOCK_ID_VAR, 'test.block');

        $testBlock2 = new ViewModel();
        $testBlock2->setOption('parent_block', $testBlock);
        $testBlock2->setTemplate('widget1');

        $this->layout->addBlock('test.block', $testBlock);
        $this->layout->addBlock('test.block2', $testBlock2);

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

        $blocks = $this->collector->getBlocks();
        $testBlockArray = current($blocks);
        $testBlock2Array = array_pop($blocks);

        $this->assertEquals(
            'test.block::content',
            $testBlock2Array['capture_to']
        );

        $this->assertContains(
            '_files/view/widget1.phtml',
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

        $this->assertEquals(
            LayoutUpdaterInterface::AREA_DEFAULT,
            $this->collector->getCurrentArea()
        );

        $this->assertInternalType(
            'array',
            $this->collector->getLayoutStructure()
        );

    }
}
