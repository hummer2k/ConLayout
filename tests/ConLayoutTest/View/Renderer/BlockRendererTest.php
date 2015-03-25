<?php

namespace ConLayoutTest\View\Renderer;

use ConLayout\Block\AbstractBlock;
use ConLayout\View\Renderer\BlockRendererFactory;
use ConLayoutTest\AbstractTest;
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
        $factory = new BlockRendererFactory();
        return $factory->createService($this->sm);
    }

    public function testMagicGetDelegationToCurrentViewModel()
    {
        $currentViewModel = new TestBlock();
        $renderer = $this->createBlockRenderer();
        $renderer->setVars(['var1' => 'My Var 1!']);
        /* @var $viewModelHelper ViewModelHelper */
        $viewModelHelper = $this->sm->get('ViewHelperManager')->get('viewModel');

        $viewModelHelper->setCurrent($currentViewModel);

        $this->assertEquals('some_stuff', $renderer->someStuff);
        $this->assertEquals('My Var 1!', $renderer->var1);
    }

    public function testRender()
    {
        $renderer = $this->createBlockRenderer();
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

    public function testCache()
    {
        $renderer = $this->createBlockRenderer();
        $renderer->setCacheEnabled(true);

        $this->assertEquals(true, $renderer->isCacheEnabled());

        $renderer->setCacheEnabled(false);

        $this->assertEquals(false, $renderer->isCacheEnabled());

        $cache = $this->getMock('Zend\Cache\Storage\StorageInterface');
        $cache->method('getItem')->willReturn($this->getRenderedHtml());

        $cacheOptions = new AdapterOptions();
        $cache->method('getOptions')->willReturn($cacheOptions);

        $renderer->setCache($cache);
        $renderer->setCacheEnabled();

        $html = $renderer->render($this->getViewModel());

        $this->assertEquals($html, $this->getRenderedHtml());

        $this->assertSame($cache, $renderer->getCache());
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