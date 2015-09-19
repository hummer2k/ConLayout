<?php

namespace ConLayoutTest\Listener;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LoadLayoutListener;
use ConLayoutTest\AbstractTest;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LoadLayoutListenerTest extends AbstractTest
{
    public function testLoadLayout()
    {
        $listener = new LoadLayoutListener($this->layout);

        $layoutModel = new ViewModel();

        $event = new \Zend\Mvc\MvcEvent();
        $event->setViewModel($layoutModel);

        $this->assertFalse($this->layout->isLoaded());

        $listener->loadLayout($event);

        $this->assertTrue($this->layout->isLoaded());

        $this->assertSame($layoutModel, $this->layout->getBlock(
            LayoutInterface::BLOCK_ID_ROOT
        ));
    }

    public function testLoadLayoutWithTerminationModel()
    {
        $listener = new LoadLayoutListener($this->layout);

        $layoutModel = new ViewModel();
        $layoutModel->setTerminal(true);

        $event = new \Zend\Mvc\MvcEvent();
        $event->setViewModel($layoutModel);

        $this->assertFalse($this->layout->isLoaded());

        $listener->loadLayout($event);

        $this->assertFalse($this->layout->isLoaded());

    }
}
