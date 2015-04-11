<?php

namespace ConLayoutTest;

use ConLayout\Handle\Handle;
use ConLayout\Updater\LayoutUpdater;
use Zend\Config\Config;
use Zend\EventManager\EventManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddHandle()
    {
        $updater = new LayoutUpdater();
        $this->assertSame([
            'default'
        ], $updater->getHandles());

        $updater->addHandle(new Handle('my-handle', 5));

        $this->assertSame([
            'default',
            'my-handle'
        ], $updater->getHandles());

        $updater->addHandle(new Handle('test-handle', 2));

        $this->assertSame([
            'default',
            'test-handle',
            'my-handle'
        ], $updater->getHandles());
    }

    public function testRemoveHandle()
    {
        $updater = new LayoutUpdater();
        $updater->addHandle(new Handle('test-handle', 2));

        $this->assertSame([
            'default',
            'test-handle'
        ], $updater->getHandles());

        $updater->removeHandle('test-handle');

        $this->assertSame([
            'default'
        ], $updater->getHandles());
    }

    public function testGetLayoutStructure()
    {
        $eventManager = new EventManager();
        $eventManager->getSharedManager()->attach(
            'ConLayout\Update\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            function($e) {
                $layout = 1;
                return new Config([
                    'default' => [
                        'blocks' => [
                            'my-block' => [
                                'capture_to' => 'sidebarLeft'
                            ]
                        ]
                    ]
                ]);
            }
        );
        $updater = new LayoutUpdater();
        $updater->setEventManager($eventManager);

        $layoutStructure = $updater->getLayoutStructure();

        $this->assertEquals([
            'blocks' => [
                'my-block' => [
                    'capture_to' => 'sidebarLeft'
                ]
            ]
        ], $layoutStructure->toArray());
    }
}
