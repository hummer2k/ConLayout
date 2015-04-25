<?php

namespace ConLayout\View\Strategy;

use ConLayout\Block\Dummy;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Bootstrap;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockStrategyTest extends AbstractTest
{
    /**
     *
     * @var BlockRendererStrategy
     */
    protected $strategy;

    /**
     *
     * @var EventManager
     */
    protected $em;

    public function setUp()
    {
        parent::setUp();
        $this->strategy = Bootstrap::getServiceManager()
            ->create('ConLayout\View\Strategy\BlockRendererStrategy');
        $this->em = Bootstrap::getServiceManager()
            ->create('EventManager');
    }

    public function testAttach()
    {
        $this->strategy->attach($this->em);

        $this->assertCount(
            1,
            $this->em->getListeners(\Zend\View\ViewEvent::EVENT_RENDERER)
        );

        $this->assertCount(
            1,
            $this->em->getListeners(\Zend\View\ViewEvent::EVENT_RESPONSE)
        );

    }

    public function testSelectRendererAndInjectResponse()
    {
        $renderer = new BlockRenderer();
        $strategy = new BlockRendererStrategy($renderer);

        $viewEvent = new \Zend\View\ViewEvent();
        $viewEvent->setModel(new ViewModel());

        $this->assertNull($strategy->selectRenderer($viewEvent));

        $dummyBlock = new Dummy();
        $viewEvent->setModel($dummyBlock);

        $this->assertSame(
            $renderer,
            $strategy->selectRenderer($viewEvent)
        );
        
        $this->assertNull($strategy->injectResponse($viewEvent));

        $response = new Response();
        $viewEvent->setRenderer($renderer);
        $viewEvent->setResult('test');
        $viewEvent->setResponse($response);
        $strategy->injectResponse($viewEvent);

        $this->assertEquals('test', $response->getContent());
    }
}