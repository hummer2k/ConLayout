<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\LayoutUpdateListener;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListenerTest extends AbstractTest
{
    public function testOnLoadGlobalLayoutStructure()
    {
        $listener = new LayoutUpdateListener([
            LayoutUpdateListener::AREA_GLOBAL => __DIR__ . '/_files/layout.{php,xml}'
        ]);

        $globalLayoutStructure = new Config([], true);

        $event = new UpdateEvent();
        $event->setGlobalLayoutStructure($globalLayoutStructure);

        $listener->onLoadGlobalLayoutStructure($event);

        $aStructure = $globalLayoutStructure->toArray();
        $blocks = $aStructure['default']['blocks'];
        
        $this->assertArrayHasKey('header', $blocks);
        $this->assertArrayHasKey('footer', $blocks);
        $this->assertArrayHasKey('widget1', $blocks);

    }

    public function testArea1()
    {
        $listener = new LayoutUpdateListener([
            LayoutUpdateListener::AREA_GLOBAL => __DIR__ . '/_files/layout.{php,xml}',
            'area1' => __DIR__ . '/_files/area1/layout.php',
            'area2' => __DIR__ . '/_files/area2/layout.php'
        ]);

        $listener->setArea('area1');

        $globalLayoutStructure = new Config([], true);

        $event = new UpdateEvent();
        $event->setGlobalLayoutStructure($globalLayoutStructure);
        $listener->onLoadGlobalLayoutStructure($event);
        $aStructure = $globalLayoutStructure->toArray();        
        $blocks = $aStructure['default']['blocks'];

        $this->assertArrayHasKey('header', $blocks);
        $this->assertArrayHasKey('footer', $blocks);
        $this->assertArrayHasKey('widget1', $blocks);
        $this->assertArrayHasKey('area1.widget', $blocks);
        $this->assertFalse(isset($blocks['area2.widget']));
    }

    public function testArea2()
    {
        $listener = new LayoutUpdateListener([
            LayoutUpdateListener::AREA_GLOBAL => __DIR__ . '/_files/layout.{php,xml}',
            'area1' => __DIR__ . '/_files/area1/layout.php',
            'area2' => __DIR__ . '/_files/area2/layout.php'
        ]);

        $listener->setArea('area2');

        $globalLayoutStructure = new Config([], true);

        $event = new UpdateEvent();
        $event->setGlobalLayoutStructure($globalLayoutStructure);
        $listener->onLoadGlobalLayoutStructure($event);
        $aStructure = $globalLayoutStructure->toArray();
        $blocks = $aStructure['default']['blocks'];

        $this->assertArrayHasKey('header', $blocks);
        $this->assertArrayHasKey('footer', $blocks);
        $this->assertArrayHasKey('widget1', $blocks);
        $this->assertArrayHasKey('area2.widget', $blocks);
        $this->assertFalse(isset($blocks['area1.widget']));
    }
}
