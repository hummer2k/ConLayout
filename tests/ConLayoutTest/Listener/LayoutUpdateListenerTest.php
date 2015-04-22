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
        $listener = new LayoutUpdateListener(
            __DIR__ . '/_files/layout.{php,xml}'
        );

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
}