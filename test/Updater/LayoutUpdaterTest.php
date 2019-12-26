<?php

namespace ConLayoutTest\Updater;

use ConLayout\Handle\Handle;
use ConLayout\Updater\Collector\CollectorInterface;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterTest extends AbstractTest
{
    protected $em;

    protected $layoutStructure;

    /**
     * @var LayoutUpdaterInterface
     */
    protected $updater;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updater = new LayoutUpdater();
        $this->em = new EventManager(new SharedEventManager());
        $this->updater->setEventManager($this->em);
        $instructions = [
            [
                'default',
                'frontend',
                new Config([
                    'blocks' => [
                        'header' => [],
                        'footer' => []
                    ]
                ], true)
            ],
            [
                'another-handle',
                'frontend',
                new Config([
                    'blocks' => [
                        'widget1' => []
                    ]
                ], true)
            ],
            [
                'handle/with-include',
                'frontend',
                new Config([
                    'blocks' => [
                        'do.not.override' => [
                            'remove' => false
                        ]
                    ],
                    LayoutUpdaterInterface::INSTRUCTION_INCLUDE => [
                        'included/handle'
                    ]
                ], true)
            ],
            [
                'included/handle',
                'frontend',
                new Config([
                    'blocks' => [
                        'some.included.block' => [
                            'template' => 'some/tpl'
                        ],
                        'do.not.override' => [
                            'remove' => true
                        ]
                    ],
                    LayoutUpdaterInterface::INSTRUCTION_INCLUDE => [
                        'included/handle/no2'
                    ]
                ], true)
            ],
            [
                'included/handle/no2',
                'frontend',
                new Config([
                    'blocks' => [
                        'some.included.block.2' => []
                    ]
                ])
            ]
        ];
        $this->em->getSharedManager()->clearListeners(
            LayoutUpdater::class
        );

        $collectorMock = $this->getMockBuilder(CollectorInterface::class)->getMock();
        $collectorMock
            ->method('collect')
            ->will($this->returnValueMap($instructions));

        $this->updater->attachCollector('mock', $collectorMock);
    }

    public function testIsReadOnly()
    {
        $layoutStructure = $this->updater->getLayoutStructure();
        $this->assertTrue($layoutStructure->isReadOnly());
    }

    public function testDefaultHandle()
    {
        $layoutStructure = $this->updater->getLayoutStructure()->toArray();
        $this->assertEquals([
            'blocks' => [
                'header' => [],
                'footer' => []
            ]
        ], $layoutStructure);
    }

    public function testAnotherHandle()
    {
        $this->updater->addHandle(new Handle('another-handle', 1));
        $this->assertEquals([
            'blocks' => [
                'header' => [],
                'footer' => [],
                'widget1' => []
            ]
        ], $this->updater->getLayoutStructure()->toArray());
    }

    public function testShortCircuiting()
    {
        $this->em->getSharedManager()->attach(
            LayoutUpdater::class,
            UpdateEvent::EVENT_COLLECT,
            function (UpdateEvent $e) {
                $e->stopPropagation();
                return new Config(['cached' => true]);
            }
        );

        $layoutStructure = $this->updater->getLayoutStructure()->toArray();

        $this->assertEquals([
            'cached' => true
        ], $layoutStructure);
    }

    public function testSetHandles()
    {
        $this->updater->setHandles([
            new Handle('handle-1', 5),
            new Handle('handle-2', 0),
            new Handle('handle-3', 2)
        ]);

        $this->assertSame([
            'handle-2',
            'handle-3',
            'handle-1'
        ], $this->updater->getHandles());
    }

    public function testSetAndGetArea()
    {
        $area = 'some-area';
        $this->layoutUpdater->setArea($area);
        $this->assertSame($area, $this->layoutUpdater->getArea());
    }

    public function testIncludedHandleRecursivly()
    {
        $this->updater->setHandles([
            new Handle('handle/with-include', 10)
        ]);
        $layoutStructure = $this->updater->getLayoutStructure();
        $block = $layoutStructure->blocks->get('some.included.block');
        $this->assertInstanceOf(Config::class, $block);
        $this->assertEquals(
            'some/tpl',
            $block->get('template')
        );
        $block2 = $layoutStructure->blocks->get('some.included.block.2');
        $this->assertInstanceOf(Config::class, $block2);
    }

    public function testIncludedHandleDoesNotOverride()
    {
        $this->updater->setHandles([
            new Handle('handle/with-include', 10)
        ]);
        $layoutStructure = $this->updater->getLayoutStructure();
        $block = $layoutStructure->blocks->get('do.not.override');
        $includedBlock = $layoutStructure->blocks->get('some.included.block');
        $this->assertEquals(
            'some/tpl',
            $includedBlock->get('template')
        );
        $this->assertFalse($block->remove);
    }
}
