<?php

namespace ConLayoutTest\Listener;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\NameActionViewModelListener;
use ConLayoutTest\AbstractTest;
use Zend\View\Model\ViewModel;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class NameActionViewModelListenerTest extends AbstractTest
{
    protected $nameActionViewModelListener;

    protected $mvcEvent;

    public function setUp()
    {
        $this->nameActionViewModelListener = new NameActionViewModelListener();
        $this->mvcEvent = new \Zend\Mvc\MvcEvent();
    }

    public function testWithViewModel()
    {
        $viewModel = new ViewModel();
        $this->mvcEvent->setResult($viewModel);

        $this->nameActionViewModelListener->nameActionViewModel($this->mvcEvent);

        $this->assertEquals(
            $viewModel->getVariable(LayoutInterface::BLOCK_ID_VAR),
            LayoutInterface::BLOCK_ID_ACTION_RESULT
        );
    }

    public function testWithNull()
    {
        $this->nameActionViewModelListener->nameActionViewModel($this->mvcEvent);
    }
}
