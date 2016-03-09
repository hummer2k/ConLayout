<?php

namespace ConLayoutTest\Block\Factory;

use ConLayout\Block\AbstractBlock;
use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\Layout\LayoutInterface;
use ConLayoutTest\AbstractTest;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\RequestInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BlockFactoryTest extends AbstractTest
{
    /**
     *
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     *
     * @var ServiceManager
     */
    protected $sm;

    public function setUp()
    {
        parent::setUp();
        $this->sm = new ServiceManager();
        $this->factory = new BlockFactory([], new BlockManager(), $this->sm);
    }

    public function testCreateBlock()
    {
        $blockSpecs = [
            'options' => [
                'option1' => 'value_option1',
                'order' => 10
            ],
            'variables' => [
                'var1' => 'value_var1'
            ],
            'template' => 'path/to/template',
            'append' => false,
            'capture_to' => 'sidebarLeft',
            'actions' => [
                'my-option' => [
                    'method' => 'setOption',
                    'name'   => 'my-option',
                    'value'  => 'value_my-option'
                ]
            ]
        ];
        $block = $this->factory->createBlock('block1', $blockSpecs);

        $this->assertEquals('path/to/template', $block->getTemplate());
        $this->assertEquals(false, $block->isAppend());
        $this->assertEquals('value_option1', $block->getOptions()['option1']);
        $this->assertEquals(10, $block->getOptions()['order']);
        $this->assertEquals('value_var1', $block->getVariable('var1'));
        $this->assertEquals('sidebarLeft', $block->captureTo());
        $this->assertEquals('value_my-option', $block->getOptions()['my-option']);
    }

    /**
     * @expectedException ConLayout\Exception\BadMethodCallException
     */
    public function testThrowsExceptionOnMissingMethod()
    {
        $specs = [
            'actions' => [
                'not-exists' => [
                    'method' => 'notExists___'
                ]
            ]
        ];

        $this->factory->createBlock('my-block', $specs);
    }

    public function testClass()
    {
        $specs = [
            'class' => MyBlock::class
        ];

        $block = $this->factory->createBlock('test-block', $specs);

        $this->assertEquals(
            'test-block',
            $block->getVariable(LayoutInterface::BLOCK_ID_VAR)
        );
    }

    public function testBlockFromBlockManager()
    {
        $blockManager = new BlockManager();
        $class = MyBlock::class;
        $blockManager->setInvokableClass(
            MyBlock::class,
            $class
        );
        $blockFactory = new BlockFactory([], $blockManager, new ServiceManager());
        $block = $blockFactory->createBlock('my.block', [
            'class' => MyBlock::class
        ]);
        $this->assertInstanceOf($class, $block);
    }

    public function testCreateBlockWithTemplate()
    {
        $block = $this->factory->createBlock('block.id', [
            'class' => TplBlock::class
        ]);
        $this->assertEquals('already/set/template', $block->getTemplate());
    }

    public function testCreateBlockImpl()
    {
        $request = new Request();
        $renderer = new PhpRenderer();
        $this->sm->setService('Request', $request);
        $this->sm->setService('ViewRenderer', $renderer);
        $block = $this->factory->createBlock('test.block.impl', [
            'class' => BlockImpl::class
        ]);
        $this->assertInstanceof(
            RequestInterface::class,
            $block->getRequest()
        );
        $this->assertSame($request, $block->getRequest());
        $this->assertSame($renderer, $block->getView());
    }
}
// @codingStandardsIgnoreStart
class TplBlock extends ViewModel
{
    protected $template = 'already/set/template';
}

class MyBlock extends ViewModel
{
    protected $initialized = false;

    public function init()
    {
        $this->initialized = true;
    }

    public function isInitialized()
    {
        return $this->initialized;
    }
}

class BlockImpl extends AbstractBlock
{

}
// @codingStandardsIgnoreEnd