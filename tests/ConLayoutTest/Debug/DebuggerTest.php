<?php

namespace ConLayoutTest;

use ConLayout\Debug\Debugger;
use ConLayout\Debug\DebuggerFactory;
use ConLayout\Layout\LayoutInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;


/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class DebuggerTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new DebuggerFactory();
        $sm = new ServiceManager();

        $this->assertInstanceOf(
            'ConLayout\Debug\Debugger',
            $factory->createService($sm)
        );
    }

    public function testAddDebugBlock()
    {
        $debugger = new Debugger();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('my-template');
        $viewModel->setVariable(
            LayoutInterface::BLOCK_ID_VAR,
            'test.block'
        );

        $debugBlock = $debugger->addDebugBlock($viewModel, 'myCaptureTo');

        $this->assertSame(
            $viewModel,
            current($debugBlock->getChildren())
        );

        $this->assertEquals(
            $debugBlock->getVariable(Debugger::VAR_BLOCK_CAPTURE_TO),
            'myCaptureTo'
        );

        $this->assertEquals(
            $debugBlock->getVariable(Debugger::VAR_BLOCK_CLASS),
            'Zend\View\Model\ViewModel'
        );

        $this->assertSame(
            $debugBlock->getVariable(Debugger::VAR_BLOCK_ORIGINAL),
            $viewModel
        );

        $this->assertEquals(
            $debugBlock->getVariable(Debugger::VAR_BLOCK_TPL),
            'my-template'
        );

        $this->assertEquals(
            $debugBlock->getVariable(Debugger::VAR_BLOCK_NAME),
            'test.block'
        );
    }
}
