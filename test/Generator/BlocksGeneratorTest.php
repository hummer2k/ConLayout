<?php

declare(strict_types=1);

namespace ConLayoutTest\Generator;

use ConLayout\Generator\BlocksGenerator;
use ConLayoutTest\AbstractTestCase;
use Laminas\Config\Config;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;

class BlocksGeneratorTest extends AbstractTestCase
{
    public function testBlockIsGenerated(): void
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'template' => 'some/template',
                ],
            ],
        ]);
        $this->blocksGenerator->generate($specs);
        $block = $this->blockRegistry->get('some.block');
        $this->assertInstanceOf(
            ModelInterface::class,
            $block,
        );
        $this->assertEquals(
            'some/template',
            $block->getTemplate(),
        );
    }

    public function testReferenceOverwritesDefaults(): void
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'template' => 'some/template',
                ],
            ],
            BlocksGenerator::INSTRUCTION_REFERENCE => [
                'some.block' => [
                    'template' => 'set/by/reference',
                ],
            ],
        ]);
        $this->blocksGenerator->generate($specs);
        $block = $this->blockRegistry->get('some.block');
        $this->assertEquals('set/by/reference', $block->getTemplate());
    }

    public function testReferenceWithNestedChildren(): void
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    BlocksGenerator::INSTRUCTION_BLOCKS => [
                        'some.child' => [],
                    ],
                ],
            ],
            BlocksGenerator::INSTRUCTION_REFERENCE => [
                'some.child' => [
                    'template' => 'test/tpl',
                ],
            ],
        ]);
        $this->blocksGenerator->generate($specs);
        $this->assertEquals(
            'test/tpl',
            $this->blockRegistry->get('some.child')->getTemplate(),
        );
    }

    public function testDoNotCreateBlockIfRemoved(): void
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'remove' => true,
                ],
                'another.block' => [],
            ],
        ]);
        $this->blocksGenerator->generate($specs);
        $this->assertInstanceOf(
            ModelInterface::class,
            $this->blockRegistry->get('another.block'),
        );
        $this->assertFalse($this->blockRegistry->get('some.block'));
    }

    public function testCreateChildren(): void
    {
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    BlocksGenerator::INSTRUCTION_BLOCKS => [
                        'some.block.child' => [],
                    ],
                ],
            ],
        ]);
        $this->blocksGenerator->generate($specs);
        $child = $this->blockRegistry->get('some.block.child');
        $this->assertInstanceOf(
            ModelInterface::class,
            $child,
        );
        $this->assertEquals(
            'some.block',
            $child->getVariable('parent'),
        );
        $this->assertTrue($child->getVariable('has_parent'));
    }

    public function testConfigureBlockIfAlreadyInPool(): void
    {
        $block = new ViewModel();
        $this->blockRegistry->add('some.block', $block);
        $specs = new Config([
            BlocksGenerator::INSTRUCTION_BLOCKS => [
                'some.block' => [
                    'template' => 'path/to/tpl',
                ],
            ],
        ]);
        $this->blocksGenerator->generate($specs);
        $this->assertSame($block, $this->blockRegistry->get('some.block'));
    }
}
