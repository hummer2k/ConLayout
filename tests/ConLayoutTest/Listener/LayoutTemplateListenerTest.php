<?php

namespace ConLayoutTest\Listener;

use ConLayout\Listener\LayoutTemplateListener;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;
use Zend\View\Model\ViewModel;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutTemplateListenerTest extends AbstractTest
{
    public function testSetLayoutTemplate()
    {
        $listener = new LayoutTemplateListener(
            $this->layoutUpdater
        );

        $layout = new ViewModel();

        $this->assertEmpty($layout->getTemplate());

        $event = new \Zend\Mvc\MvcEvent();
        $event->setViewModel($layout);

        $listener->setLayoutTemplate($event);

        $this->assertEquals('2cols-left', $layout->getTemplate());
    }

     public function testAlreadySetLayoutTemplate()
    {
        $listener = new LayoutTemplateListener(
            $this->layoutUpdater
        );

        $layout = new ViewModel();
        $layout->setTemplate('3cols');

        $event = new \Zend\Mvc\MvcEvent();
        $event->setViewModel($layout);

        $listener->setLayoutTemplate($event);

        $this->assertEquals('3cols', $layout->getTemplate());
    }

    protected function getLayoutStructure()
    {
        return new Config([
            'layout' => '2cols-left'
        ]);
    }
}