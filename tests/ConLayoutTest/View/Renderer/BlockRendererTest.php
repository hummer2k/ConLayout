<?php

namespace ConLayoutTest\View\Renderer;

use ConLayout\Block\AbstractBlock;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Bootstrap;
use Zend\View\Helper\ViewModel as ViewModelHelper;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererTest extends AbstractTest
{
    protected $blockRenderer;

    public function setUp()
    {
        parent::setUp();
        $this->blockRenderer = Bootstrap::getServiceManager()
            ->create('ConLayout\View\Renderer\BlockRenderer');
    }

    /**
     *
     * @return BlockRenderer
     */
    protected function getBlockRenderer()
    {
        return $this->blockRenderer;
    }

    public function testMagicDelegationToCurrentViewModel()
    {
        $currentViewModel = new TestBlock();
        $renderer = $this->getBlockRenderer();
        /* @var $viewModelHelper ViewModelHelper */
        $viewModelHelper = Bootstrap::getServiceManager()
            ->get('ViewHelperManager')
            ->get('viewModel');

        $viewModelHelper->setCurrent($currentViewModel);

        $this->assertEquals('some_stuff', $renderer->getSomeStuff());

        $this->assertInstanceOf(
            'Zend\View\Helper\HelperInterface',
            $renderer->headLink()
        );
    }

    public function testRender()
    {
        $renderer = $this->getBlockRenderer();
        $html = $renderer->render($this->getViewModel());

        $this->assertEquals(
            $this->getRenderedHtml(),
            $html
        );

        $html = $renderer->render($this->getViewModel());
        $this->assertEquals(
            $this->getRenderedHtml(),
            $html
        );

    }

    public function testRenderWithCachePre()
    {
        $renderer = clone $this->getBlockRenderer();
        $em = clone Bootstrap::getServiceManager()
            ->get('EventManager');
        $renderer->setEventManager($em);
        $em->getSharedManager()->attach(
            'ConLayout\View\Renderer\BlockRenderer',
            'render.pre',
            function($e) {
                return 'cached';
            }
        );
        $this->assertEquals('cached', $renderer->render($this->getViewModel()));
    }

    public function testRenderWithCachePost()
    {
        $renderer = $this->getBlockRenderer();
        $em = $renderer->getEventManager();
        
        $em->getSharedManager()
            ->attach(
            'ConLayout\View\Renderer\BlockRenderer',
            'render.post',
            function($e) {
                $result = $e->getParam('__RESULT__');
                $this->assertEquals($result, $this->getRenderedHtml());
            }
        );

        $renderer->render($this->getViewModel());
    }

    protected function getViewModel()
    {
        $viewModel =  new TestBlock([
            'title' => 'Lorem Ipsum',
            'content' => 'Dolor sit amet.'
        ]);
        $viewModel->setTemplate('blocks/render-test');
        return $viewModel;
    }

    protected function getRenderedHtml()
    {
        return file_get_contents(__DIR__ . '/../../_files/render-test.html');
    }
}

class TestBlock extends AbstractBlock
{
    public function getSomeStuff()
    {
        return 'some_stuff';
    }

    public function getCacheTtl()
    {
        return 60;
    }
}