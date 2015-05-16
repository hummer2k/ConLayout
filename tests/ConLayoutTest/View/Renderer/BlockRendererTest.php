<?php

namespace ConLayoutTest\View\Renderer;

use ConLayout\Block\AbstractBlock;
use ConLayout\View\Renderer\BlockRenderer;
use ConLayoutTest\AbstractTest;
use ConLayoutTest\Bootstrap;
use Zend\View\Helper\ViewModel as ViewModelHelper;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\TemplatePathStack;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockRendererTest extends AbstractTest
{
    /**
     *
     * @var BlockRenderer
     */
    protected $blockRenderer;

    protected $resolver;

    public function setUp()
    {
        parent::setUp();
        $this->blockRenderer = Bootstrap::getServiceManager()
            ->create('ConLayout\View\Renderer\BlockRenderer');

        $this->blockRenderer = new BlockRenderer();

        $this->resolver = new TemplatePathStack();
        $this->resolver->addPath(__DIR__ . '/_view');
        $this->blockRenderer->setResolver($this->resolver);
        $this->blockRenderer->setHelperPluginManager(
            Bootstrap::getServiceManager()->get('ViewHelperManager')
        );
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

        $em->getSharedManager()->clearListeners('ConLayout\View\Renderer\BlockRenderer');
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

        $em->getSharedManager()->clearListeners('ConLayout\View\Renderer\BlockRenderer');
    }

    protected function getViewModel()
    {
        $viewModel =  new TestBlock([
            'title' => 'Lorem Ipsum',
            'content' => 'Dolor sit amet.'
        ]);
        $viewModel->setTemplate('render-test');
        return $viewModel;
    }

    protected function getRenderedHtml()
    {
        return file_get_contents(__DIR__ . '/../../_files/render-test.html');
    }

    public function testRenderChildren()
    {
        $block1 = new ViewModel();
        $block1->setTemplate('test');
        $block2 = new ViewModel();
        $block2->setTemplate('test/child1');

        $block1->addChild($block2, 'childHtml');

        $block3 = new ViewModel();
        $block3->setTemplate('test/child2');

        $block2->addChild($block3, 'childHtml');

        $rendered = $this->blockRenderer->render($block1);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../_files/children-without-tree.html'),
            $rendered
        );

        $this->blockRenderer->setCanRenderTrees(true);
        $rendered = $this->blockRenderer->render($block1);

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../_files/children-with-tree.html'),
            $rendered
        );
    }

    public function testRenderChildrenAppend()
    {
        $parent = new ViewModel();
        $parent->setTemplate('test');

        $child1 = new ViewModel();
        $child1->setTemplate('test/child1');

        $child2 = new ViewModel();
        $child2->setTemplate('test/child2');

        $parent->addChild($child1, 'childHtml', true);
        $parent->addChild($child2, 'childHtml', true);

        $this->blockRenderer->setCanRenderTrees(true);

        $rendered = $this->blockRenderer->render($parent);

        $this->assertEquals(
            file_get_contents( __DIR__ . '/../../_files/children-append.html'),
            $rendered
        );
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