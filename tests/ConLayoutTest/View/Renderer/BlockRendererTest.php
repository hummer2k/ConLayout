<?php

namespace ConLayoutTest\View\Renderer;

use ConLayout\Block\AbstractBlock;
use ConLayout\View\Renderer\BlockRendererFactory;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Bootstrap;
use Zend\Cache\Storage\Adapter\AdapterOptions;
use Zend\View\Helper\ViewModel as ViewModelHelper;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererTest extends AbstractTest
{
    protected function createBlockRenderer()
    {
        return Bootstrap::getServiceManager()
            ->get('ConLayout\View\Renderer\BlockRenderer');
    }

    public function testMagicDelegationToCurrentViewModel()
    {
        $currentViewModel = new TestBlock();
        $renderer = $this->createBlockRenderer();
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
        $renderer = $this->createBlockRenderer();
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