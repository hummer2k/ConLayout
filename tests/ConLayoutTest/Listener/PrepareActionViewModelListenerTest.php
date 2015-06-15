<?php

namespace ConLayoutTest\Listener;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\PrepareActionViewModelListener;
use ConLayoutTest\AbstractTest;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class PrepareActionViewModelListenerTest extends AbstractTest
{
    protected $prepareActionViewModelListener;

    protected $mvcEvent;

    public function setUp()
    {
        $this->prepareActionViewModelListener = new PrepareActionViewModelListener();
        $this->mvcEvent = new \Zend\Mvc\MvcEvent();
    }

    public function testWithViewModel()
    {
        $viewModel = new ViewModel();
        $this->mvcEvent->setResult($viewModel);

        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);

        $this->assertEquals(
            $viewModel->getVariable(LayoutInterface::BLOCK_ID_VAR),
            LayoutInterface::BLOCK_ID_ACTION_RESULT
        );
    }

    public function testWithNull()
    {
        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);
    }

    public function testIsAppend()
    {
        $viewModel = new ViewModel();
        $this->mvcEvent->setResult($viewModel);

        $this->assertFalse($viewModel->isAppend());
        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);
        $this->assertTrue($viewModel->isAppend());
    }

    public function testDoNothingOnTerminal()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setAppend(false);

        $this->mvcEvent->setResult($viewModel);
        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);

        $this->assertFalse($viewModel->isAppend());
        $this->assertNull($viewModel->getVariable(LayoutInterface::BLOCK_ID_VAR));

    }

    public function testDoNotSetBlockIdIfAlreadySet()
    {
        $viewModel = new ViewModel();
        $viewModel->setVariable(LayoutInterface::BLOCK_ID_VAR, 'the.block');

        $this->mvcEvent->setResult($viewModel);
        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);

        $this->assertEquals('the.block', $viewModel->getVariable(LayoutInterface::BLOCK_ID_VAR));
    }
}
