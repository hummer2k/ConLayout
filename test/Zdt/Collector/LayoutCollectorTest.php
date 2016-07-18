<?php

namespace ConLayoutTest\Zdt\Collector;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Handle\HandleInterface;
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
        if (!class_exists('ZendDeveloperTools\Collector\AbstractCollector')) {
            $this->markTestSkipped('ZDT not available');
        }
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
            LayoutUpdaterInterface::class,
            new LayoutUpdater()
        );
        $serviceManager->setService(
            LayoutInterface::class,
            new Layout(
                $this->layoutUpdater,
                $this->blockPool
            )
        );
        $serviceManager->setService(
            'ViewResolver',
            new AggregateResolver()
        );

        $instance = $layoutCollectorFactory($serviceManager, LayoutCollector::class);
        $this->assertInstanceOf(
            LayoutCollector::class,
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

        $testBlock2 = new ViewModel();
        $testBlock2->setOption('parent', 'test.block');
        $testBlock2->setTemplate('widget1');

        $this->blockPool->add('test.block', $testBlock);
        $this->blockPool->add('test.block2', $testBlock2);

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
            HandleInterface::class,
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
            ViewModel::class,
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
