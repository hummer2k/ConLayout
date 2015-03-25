<?php

namespace ConLayout\View\Strategy;

use ConLayout\Block\Dummy;
use ConLayoutTest\AbstractTest;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockStrategyTest extends AbstractTest
{
    protected function createStrategy()
    {
        $factory = new BlockRendererStrategyFactory();
        return $factory->createService($this->sm);
    }

    public function testAttach()
    {
        $strategy = $this->createStrategy();
        $strategy->attach($this->sm->get('EventManager'));
    }

    public function testSelectInject()
    {
        $strategy = $this->createStrategy();

        $viewEvent = new \Zend\View\ViewEvent();
        $viewEvent->setModel(new ViewModel());

        $this->assertNull($strategy->selectRenderer($viewEvent));

        $dummyBlock = new Dummy();
        $viewEvent->setModel($dummyBlock);

        $this->assertInstanceOf('ConLayout\View\Renderer\BlockRenderer', $strategy->selectRenderer($viewEvent));
        $this->assertInstanceOf('Zend\View\Renderer\RendererInterface', $strategy->selectRenderer($viewEvent));

        $this->assertNull($strategy->injectResponse($viewEvent));

        $response = new Response();
        $viewEvent->setRenderer($this->sm->get('ConLayout\View\Renderer\BlockRenderer'));
        $viewEvent->setResult('test');
        $viewEvent->setResponse($response);
        $strategy->injectResponse($viewEvent);

        $this->assertEquals('test', $response->getContent());



    }
}