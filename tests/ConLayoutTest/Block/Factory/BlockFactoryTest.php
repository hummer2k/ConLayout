<?php

namespace ConLayoutTest\Block\Factory;

use ConLayout\Block\AbstractBlock;
use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\Layout\LayoutInterface;
use ConLayoutTest\AbstractTest;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceManager;
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
                'setOption' => [
                    'my-option', 'value_my-option'
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

    public function testMultipleActions()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'actions' => [
                'set_some_option' => [
                    'setOption' => ['my-option', 'value_my-option']
                ],
                'set_another_option' => [
                    'setOption' => ['some-other-option', 'some-option-value']
                ],
                'setVariable' => ['testVar', 'valueTestVar'],
                'set_some_var' => [
                    'setVariable' => ['testVar2', 'SOME_VAR']
                ],
                'set_another_var' => [
                    'setVariable' => ['testVar3', 'ANOTHER_VAR']
                ]
            ]
        ];

        $block = $factory->createBlock('block.id', $specs);

        $this->assertEquals('value_my-option', $block->getOption('my-option'));
        $this->assertEquals('some-option-value', $block->getOption('some-other-option'));
        $this->assertEquals('valueTestVar', $block->getVariable('testVar'));
        $this->assertEquals('SOME_VAR', $block->getVariable('testVar2'));
        $this->assertEquals('ANOTHER_VAR', $block->getVariable('testVar3'));
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

    public function testWrapBlockArray()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'template' => 'my/tpl',
            'wrapper' => [
                'template'   => 'my/wrapper',
                'html_class' => 'my-wrapper-class',
                'html_tag'   => 'div'
            ]
        ];

        $block = $factory->createBlock('my-block', $specs);

        $this->assertEquals('my/wrapper', $block->getTemplate());

        $this->assertEquals('my-wrapper-class', $block->getVariable('htmlWrapperClass'));
        $this->assertEquals('div', $block->getVariable('htmlWrapperTag'));
    }

    public function testWrapBlockArrayWithoutTemplate()
    {
        $factory = new BlockFactory();
        $factory->setServiceLocator(new ServiceManager());

        $specs = [
            'template' => 'my/tpl',
            'wrapper' => [
                'html_tag'   => 'div'
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
            'class' => __NAMESPACE__ . '\MyBlock'
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
        $blockManager = new \ConLayout\BlockManager();
        $class = 'ConLayoutTest\Block\Factory\MyBlock';
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
            'class' => 'ConLayoutTest\Block\Factory\TplBlock'
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
            'class' => 'ConLayoutTest\Block\Factory\BlockImpl'
        ]);
        $this->assertInstanceof(
            'Zend\Stdlib\RequestInterface',
            $block->getRequest()
        );
        $this->assertSame($request, $block->getRequest());
        $this->assertSame($renderer, $block->getView());
    }
}

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
