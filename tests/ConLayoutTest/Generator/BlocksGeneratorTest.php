<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayoutTest\Generator;


use ConLayout\Generator\BlocksGenerator;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

class BlocksGeneratorTest extends AbstractTest
{
    public function testBlockIsGenerated()
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'template' => 'some/template'
                ]
            ]
        ]);
        $this->blocksGenerator->generate($specs);
        $block = $this->blockPool->get('some.block');
        $this->assertInstanceOf(
            ModelInterface::class,
            $block
        );
        $this->assertEquals(
            'some/template',
            $block->getTemplate()
        );
    }

    public function testReferenceOverwritesDefaults()
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'template' => 'some/template'
                ]
            ],
            BlocksGenerator::INSTRUCTION_REFERENCE => [
                'some.block' => [
                    'template' => 'set/by/reference'
                ]
            ]
        ]);
        $this->blocksGenerator->generate($specs);
        $block = $this->blockPool->get('some.block');
        $this->assertEquals('set/by/reference', $block->getTemplate());
    }

    public function testReferenceWithNestedChildren()
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    BlocksGenerator::INSTRUCTION_BLOCKS => [
                        'some.child' => []
                    ]
                ]
            ],
            BlocksGenerator::INSTRUCTION_REFERENCE => [
                'some.child' => [
                    'template' => 'test/tpl'
                ]
            ]
        ]);
        $this->blocksGenerator->generate($specs);
        $this->assertEquals(
            'test/tpl',
            $this->blockPool->get('some.child')->getTemplate()
        );
    }

    public function testDoNotCreateBlockIfRemoved()
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'remove' => true
                ],
                'another.block' => []
            ]
        ]);
        $this->blocksGenerator->generate($specs);
        $this->assertInstanceOf(
            ModelInterface::class,
            $this->blockPool->get('another.block')
        );
        $this->assertFalse($this->blockPool->get('some.block'));
    }

    public function testCreateChildren()
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    BlocksGenerator::INSTRUCTION_BLOCKS => [
                        'some.block.child' => []
                    ]
                ]
            ]
        ]);
        $this->blocksGenerator->generate($specs);
        $child = $this->blockPool->get('some.block.child');
        $this->assertInstanceOf(
            ModelInterface::class,
            $child
        );
        $this->assertEquals(
            'some.block',
            $child->getOption('parent')
        );
        $this->assertTrue($child->getOption('has_parent'));
    }

    public function testConfigureBlockIfAlreadyInPool()
    {
        $block = new ViewModel();
        $this->blockPool->add('some.block', $block);
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'template' => 'path/to/tpl'
                ]
            ]
        ]);
        $this->blocksGenerator->generate($specs);
        $this->assertSame($block, $this->blockPool->get('some.block'));
    }

}
