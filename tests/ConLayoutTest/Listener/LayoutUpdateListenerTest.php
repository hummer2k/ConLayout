<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;
use Zend\EventManager\EventManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListenerTest extends AbstractTest
{
    protected $listener;

    protected $layoutStructure;

    public function setUp()
    {
        parent::setUp();
        $this->listener = new LayoutUpdateListener([
            LayoutUpdateListener::AREA_GLOBAL => [
                __DIR__ . '/_files/module1',
                __DIR__ . '/_files/module2'
            ],
            'area1' => [
                __DIR__ . '/_files/module1/area1'
            ],
            'area2' => [
                __DIR__ . '/_files/module1/area2'
            ]
        ]);
        $this->layoutStructure = new Config([], true);
    }

    protected function getEvent($handle = 'default')
    {
        $event = new UpdateEvent();
        $event->setHandles([$handle]);
        $event->setLayoutStructure($this->layoutStructure);
        return $event;
    }

    public function testAttach()
    {
        $eventManager = new EventManager();
        $eventManager->getSharedManager()->clearListeners('ConLayout\Updater\LayoutUpdater');

        $this->listener->attach($eventManager);

        $listeners = $eventManager->getSharedManager()->getListeners(
            'ConLayout\Updater\LayoutUpdater',
            'getLayoutStructure.pre'
        );

        $this->assertCount(1, $listeners);
    }

    public function testFetchDefault()
    {
        $event = $this->getEvent();
        $this->listener->fetch($event);

        $layoutStructure = $event->getLayoutStructure()->toArray();

        $this->assertEquals([
            'blocks' => [
                'header' => [],
                'footer' => [],
                'widget1' => [],
                'widget2' => []
            ]
        ], $layoutStructure);
    }

    public function testFetchInclude()
    {
        $event = $this->getEvent('include');
        $this->listener->fetch($event);

        $layoutStructure = $event->getLayoutStructure()->toArray();

        $this->assertEquals([
            'blocks' => [
                'widget.included' => [],
                'another.handle.block' => []
            ],
            'include' => [
                'included-handle',
                'another-handle'
            ]
        ], $layoutStructure);
    }

    public function testFetchArea1()
    {
        $event = $this->getEvent();
        $this->listener->setArea('area1');
        $this->listener->fetch($event);

        $layoutStructure = $event->getLayoutStructure()->toArray();

        $this->assertEquals([
            'blocks' => [
                'header' => [
                    'template' => 'area1-header-tpl'
                ],
                'footer' => [],
                'widget1' => [],
                'widget2' => []
            ]
        ], $layoutStructure);
    }

    public function testFetchArea2()
    {
        $event = $this->getEvent();
        $this->listener->setArea('area2');
        $this->listener->fetch($event);

        $layoutStructure = $event->getLayoutStructure()->toArray();

        $this->assertEquals([
            'blocks' => [
                'header' => [],
                'footer' => [
                    'template' => 'area2-footer-tpl'
                ],
                'widget1' => [],
                'widget2' => []
            ]
        ], $layoutStructure);
    }
}
