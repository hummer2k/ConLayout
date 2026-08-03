<?php

declare(strict_types=1);

namespace ConLayoutTest\Listener;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\LoadLayoutListener;
use ConLayoutTest\AbstractTestCase;
use Laminas\View\Model\ViewModel;

class LoadLayoutListenerTest extends AbstractTestCase
{
    public function testLoadLayout(): void
    {
        $listener = new LoadLayoutListener($this->layout);

        $layoutModel = new ViewModel();

        $event = new \Laminas\Mvc\MvcEvent();
        $event->setViewModel($layoutModel);

        $this->layout->setRoot($layoutModel);

        $this->assertFalse($this->layout->isLoaded());

        $listener->loadLayout($event);

        $this->assertTrue($this->layout->isLoaded());

        $this->assertSame($layoutModel, $this->layout->getBlock(
            LayoutInterface::BLOCK_ID_ROOT
        ));
    }

    public function testLoadLayoutWithTerminationModel(): void
    {
        $listener = new LoadLayoutListener($this->layout);

        $layoutModel = new ViewModel();
        $layoutModel->setTerminal(true);

        $event = new \Laminas\Mvc\MvcEvent();
        $event->setViewModel($layoutModel);

        $this->assertFalse($this->layout->isLoaded());

        $listener->loadLayout($event);

        $this->assertFalse($this->layout->isLoaded());
    }
}
