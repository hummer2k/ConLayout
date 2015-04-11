<?php

namespace ConLayoutTest;

use ConLayout\Listener\ConfigCollectorListener;
use ConLayout\Update\Update;
use Zend\EventManager\EventManager;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdateEm()
    {
        $eventManager = new EventManager();

        $listener = new ConfigCollectorListener(
            ['./app/design/*/layout.{config,bla}.{xml,php}']
        );

        $eventManager->getSharedManager()->attach(
            'ConLayout\Update\Update', 'loadGlobalLayoutStructure', array($listener, 'onLoadGlobalLayoutStructure')
        );

        $update = new Update();
        $update->setEventManager($eventManager);

        $result = $update->getLayoutStructure()->toArray();

        $this->assertSame([
            'blocks' => [
                'test.asdf.xml' => [
                    'class' => 'MyCustomBlock',
                    'capture_to' => 'test.asdf::content'
                ],
                'test.asdf' => [
                    'capture_to' => 'sidebarLeft'
                ],
                'bla.asdf' => [
                    'capture_to' => 'test.asdf::childHtml'
                ]
            ]
        ], $result);
    }

    public function testApplyFor()
    {
        $eventManager = new EventManager();

        $listener = new ConfigCollectorListener(
            ['./app/design/*/layout.{config,bla}.{xml,php}']
        );

        $eventManager->getSharedManager()->attach(
            'ConLayout\Update\Update', 'loadGlobalLayoutStructure', array($listener, 'onLoadGlobalLayoutStructure')
        );

        $update = new Update();
        $update->setEventManager($eventManager);
        $update->addHandle(new \ConLayout\Handle\Handle('test-handle', 1));

        $result = $update->getLayoutStructure()->get('blocks')->toArray();

        $this->assertSame([
            'test.asdf.xml' => [
                'class' => 'MyCustomBlock',
                'capture_to' => 'test.asdf::content'
            ],
            'test.asdf' => [
                'capture_to' => 'sidebarLeft'
            ],
            'bla.asdf' => [
                'capture_to' => 'test.asdf::childHtml'
            ],
            'test.blabla' => [
                'capture_to' => 'content'
            ],
            'test.asdf234' => []
        ], $result);
    }
}
