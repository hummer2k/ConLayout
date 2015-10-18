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
        $this->factory = new BlockFactory();
        $this->sm = new ServiceManager();
        $this->factory->setServiceLocator($this->sm);
    }

    public function testCreateBlock()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());
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
        $block = $factory->createBlock('block1', $blockSpecs);

        $this->assertEquals('path/to/template', $block->getTemplate());
        $this->assertEquals(false, $block->isAppend());
        $this->assertEquals('value_option1', $block->getOptions()['option1']);
        $this->assertEquals(10, $block->getOptions()['order']);
        $this->assertEquals('value_var1', $block->getVariable('var1'));
        $this->assertEquals('sidebarLeft', $block->captureTo());
        $this->assertEquals('value_my-option', $block->getOptions()['my-option']);
    }

    public function testWrapBlockString()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'template' => 'my/tpl',
            'wrapper' => 'my/wrapper'
        ];

        $block = $factory->createBlock('my-block', $specs);

        $this->assertEquals('my/wrapper', $block->getTemplate());

    }

    /**
     * @expectedException ConLayout\Exception\BadMethodCallException
     */
    public function testThrowsExceptionOnMissingMethod()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'actions' => [
                'not-exists' => [
                    'method' => 'notExists___'
                ]
            ]
        ];

        $factory->createBlock('my-block', $specs);
    }

    public function testWrapBlockArray()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'template' => 'my/tpl',
            'wrapper' => [
                'template' => 'my/wrapper',
                'class' => 'my-wrapper-class',
                'tag'   => 'p'
            ]
        ];

        $block = $factory->createBlock('my-block', $specs);

        $this->assertEquals('my/wrapper', $block->getTemplate());

        $this->assertEquals(['class' => 'my-wrapper-class'], $block->getVariable('wrapperAttributes'));
        $this->assertEquals('p', $block->getVariable('wrapperTag'));
    }

    public function testWrapBlockArrayWithoutTemplate()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'template' => 'my/tpl',
            'wrapper' => [
                'tag'   => 'div'
            ]
        ];

        $block = $factory->createBlock('my-block', $specs);

        $this->assertEquals(BlockFactory::WRAPPER_DEFAULT, $block->getTemplate());
    }

    public function testClass()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'class' => MyBlock::class
        ];

        $block = $factory->createBlock('test-block', $specs);
        $this->assertTrue($block->isInitialized());

        $this->assertEquals(
            'test-block',
            $block->getVariable(LayoutInterface::BLOCK_ID_VAR)
        );
    }

    public function testBlockFromSm()
    {
        $serviceManager = new ServiceManager();
        $block = new ViewModel();
        $serviceManager->setService('Test\Block', $block);

        $factory = new BlockFactory();
        $factory->setServiceLocator($serviceManager);

        $specs = [
            'class' => 'Test\Block'
        ];
        $this->assertSame(
            $block,
            $factory->createBlock('test', $specs)
        );

    }

    public function testBlockFromBlockManager()
    {
        $blockManager = new BlockManager();
        $class = MyBlock::class;
        $blockManager->setInvokableClass(
            'MyBlock',
            $class
        );
        $blockFactory = new BlockFactory([], $blockManager);
        $block = $blockFactory->createBlock('my.block', [
            'class' => 'MyBlock'
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