<?php

namespace ConLayoutTest\Listener;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\PrepareActionViewModelListener;
use ConLayoutTest\AbstractTest;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class PrepareActionViewModelListenerTest extends AbstractTest
{
    protected $prepareActionViewModelListener;

    protected $mvcEvent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareActionViewModelListener = new PrepareActionViewModelListener(
            $this->blockPool
        );
        $this->mvcEvent = new MvcEvent();
    }

    public function testWithViewModel()
    {
        $viewModel = new ViewModel();
        $this->mvcEvent->setResult($viewModel);

        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);

        $this->assertEquals(
            $viewModel->getOption('block_id'),
            LayoutInterface::BLOCK_ID_ACTION_RESULT
        );
    }

    public function testWithNull()
    {
        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);

        $this->assertNull($this->mvcEvent->getResult());
    }

    public function testDoNothingOnTerminal()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setAppend(false);

        $this->mvcEvent->setResult($viewModel);
        $this->prepareActionViewModelListener->prepareActionViewModel($this->mvcEvent);

        $this->assertFalse($viewModel->isAppend());
        $this->assertNull($viewModel->getOption('block_id'));
    }
}
