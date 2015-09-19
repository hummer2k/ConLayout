<?php

namespace ConLayoutTest\Updater;

use ConLayout\Handle\Handle;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;
use Zend\EventManager\EventManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterTest extends AbstractTest
{
    protected $em;

    protected $layoutStructure;

    public function setUp()
    {
        parent::setUp();
        $this->updater = new LayoutUpdater();
        $this->em = new EventManager();
                $instructions = [
            'default' => [
                'blocks' => [
                    'header' => [],
                    'footer' => []
                ]
            ],
            'another-handle' => [
                'blocks' => [
                    'widget1' => []
                ]
            ]
                ];
                $this->em->getSharedManager()->clearListeners(
                    'ConLayout\Updater\LayoutUpdater'
                );
                $this->em->getSharedManager()->attach(
                    'ConLayout\Updater\LayoutUpdater',
                    'getLayoutStructure.pre',
                    function (UpdateEvent $e) use ($instructions) {
                        $handles = $e->getHandles();
                        $this->layoutStructure = $e->getLayoutStructure();
                        foreach ($handles as $handle) {
                            if (isset($instructions[$handle])) {
                                $instructionsConfig = new Config(
                                    $instructions[$handle]
                                );
                                $this->layoutStructure->merge($instructionsConfig);
                            }
                        }
                    }
                );
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
            'ConLayout\Updater\LayoutUpdater',
            'getLayoutStructure.pre',
            function (UpdateEvent $e) {
                return new Config(['cached' => true]);
            }
        );

        $layoutStructure = $this->updater->getLayoutStructure()->toArray();

        $this->assertEquals([
            'cached' => true
        ], $layoutStructure);
    }

    public function testUpdateEvent()
    {
        $this->em->getSharedManager()->clearListeners('ConLayout\Updater\LayoutUpdater');
        $this->em->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'getLayoutStructure.pre',
            function (UpdateEvent $e) {
                $this->assertEquals([
                    'default'
                ], $e->getHandles());
                $testLayoutStructure = new Config(['test' => 'test']);
                $e->getLayoutStructure()->merge($testLayoutStructure);
            }
        );
        $layoutStructure = $this->updater->getLayoutStructure()->toArray();

        $this->assertEquals([
            'test' => 'test'
        ], $layoutStructure);
    }

    public function testSetHandles()
    {
        $this->layoutUpdater->setHandles([
            new Handle('handle-1', 5),
            new Handle('handle-2', 0),
            new Handle('handle-3', 2)
        ]);

        $this->assertSame([
            'handle-2',
            'handle-3',
            'handle-1'
        ], $this->layoutUpdater->getHandles());
    }

    public function testSetAndGetArea()
    {
        $area = 'some-area';
        $this->layoutUpdater->setArea($area);
        $this->assertSame($area, $this->layoutUpdater->getArea());
    }
}
