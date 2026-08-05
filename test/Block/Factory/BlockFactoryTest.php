<?php

declare(strict_types=1);

namespace ConLayoutTest\Block\Factory;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Block\Factory\BlockFactoryInterface;
use ConLayout\BlockManager;
use ConLayout\Exception\BadMethodCallException;
use ConLayoutTest\AbstractTestCase;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;

class BlockFactoryTest extends AbstractTestCase
{
    protected BlockFactoryInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sm = new ServiceManager();
        $this->factory = new BlockFactory([], new BlockManager($this->sm));
    }

    public function testCreateBlock(): void
    {
        $blockSpecs = [
            'options' => [
                'option1' => 'value_option1',
                'order' => 10,
            ],
            'variables' => [
                'var1' => 'value_var1',
            ],
            'template' => 'path/to/template',
            'append' => false,
            'capture_to' => 'sidebarLeft',
            'actions' => [
                'my-option' => [
                    'method' => 'setVariable',
                    'name'   => 'my-option',
                    'value'  => 'value_my-option',
                ],
            ],
        ];
        $block = $this->factory->createBlock('block1', $blockSpecs);

        $this->assertEquals('path/to/template', $block->getTemplate());
        $this->assertEquals(false, $block->isAppend());
        $this->assertEquals('value_option1', $block->getVariable('option1'));
        $this->assertEquals(10, $block->getVariable('order'));
        $this->assertEquals('value_var1', $block->getVariable('var1'));
        $this->assertEquals('sidebarLeft', $block->captureTo());
        $this->assertEquals('value_my-option', $block->getVariable('my-option'));
    }

    public function testThrowsExceptionOnMissingMethod(): void
    {
        $specs = [
            'actions' => [
                'not-exists' => [
                    'method' => 'notExists___',
                ],
            ],
        ];

        $this->expectException(BadMethodCallException::class);
        $this->factory->createBlock('my-block', $specs);
    }

    public function testClass(): void
    {
        $specs = [
            'class' => ViewModel::class,
        ];

        $block = $this->factory->createBlock('test-block', $specs);

        $this->assertEquals('test-block', $block->getVariable('block_id'));
    }

    public function testBlockFromBlockManager(): void
    {
        $blockManager = new BlockManager($this->sm);
        $class = ViewModel::class;
        $blockManager->setInvokableClass(
            ViewModel::class,
            $class,
        );
        $blockFactory = new BlockFactory([], $blockManager);
        $block = $blockFactory->createBlock('my.block', [
            'class' => ViewModel::class,
        ]);
        $this->assertInstanceOf($class, $block);
    }

    public function testCreateBlockWithTemplate(): void
    {
        $block = new ViewModel();
        $block->setTemplate('already/set/template');
        $block = $this->factory->configure($block, []);
        $this->assertEquals('already/set/template', $block->getTemplate());
    }
}
