<?php

namespace ConLayoutTest;

use ConLayout\Debugger;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class DebuggerTest extends AbstractTest
{
    public function testSetEnabled()
    {
        $debugger = new Debugger();
        $debugger->setEnabled(true);

        $this->assertEquals(true, $debugger->isEnabled());

        $debugger->setEnabled(false);

        $this->assertEquals(false, $debugger->isEnabled());
    }

    public function testAddDebugBlock()
    {
        $viewModel = new ViewModel(array(
            'my_var' => 'value_of_my_var'
        ));

        $debugger = new Debugger();

        $debugBlock = $debugger->addDebugBlock($viewModel, 'sidebarLeft');

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $debugBlock);

        $originalBlock = $debugBlock->getChildren()[0];

        $this->assertSame($originalBlock, $viewModel);
    }

    public function testFactory()
    {
        $debuggerFactory = new \ConLayout\DebuggerFactory();
        $debugger = $debuggerFactory->createService($this->sm);

        $this->assertInstanceOf('ConLayout\Debugger', $debugger);

    }
}