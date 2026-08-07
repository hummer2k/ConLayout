<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector\Element;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Updater\Collector\Element\BlockElement;
use PHPUnit\Framework\TestCase;

class BlockElementTest extends TestCase
{
    private BlockElement $element;

    protected function setUp(): void
    {
        $this->element = new BlockElement();
    }

    public function testNameAndInstruction(): void
    {
        $this->assertSame('block', $this->element->name());
        $this->assertSame('blocks', $this->element->instruction());
    }

    public function testNormalizeMapsIdToKeyAndDropsIt(): void
    {
        $this->assertSame([
            'header' => [
                'template' => 'app::layout/header',
            ],
        ], $this->element->normalize([
            'id' => 'header',
            'template' => 'app::layout/header',
        ]));
    }

    public function testInsertionMetadataIsMovedIntoVariables(): void
    {
        $this->assertSame([
            'header' => [
                'template' => 't',
                'variables' => [
                    'order' => '1',
                    'parentId' => 'root',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'header',
            'order' => '1',
            'parentId' => 'root',
            'template' => 't',
        ]));
    }

    public function testNestedBlocksAreNormalizedRecursively(): void
    {
        $this->assertSame([
            'root' => [
                'template' => 'base',
                'blocks' => [
                    'header' => [
                        'template' => 'h',
                        'blocks' => [
                            'main.nav' => [
                                'template' => 'n',
                            ],
                        ],
                    ],
                ],
            ],
        ], $this->element->normalize([
            'id' => 'root',
            'template' => 'base',
            'block' => [
                'id' => 'header',
                'template' => 'h',
                'block' => [
                    'id' => 'main.nav',
                    'template' => 'n',
                ],
            ],
        ]));
    }

    public function testLegacyAndTypedChildrenMerge(): void
    {
        $this->assertSame([
            'root' => [
                'blocks' => [
                    'footer' => [
                        'template' => 'f',
                    ],
                    'header' => [
                        'template' => 't',
                    ],
                ],
            ],
        ], $this->element->normalize([
            'id' => 'root',
            'blocks' => [
                'footer' => [
                    'template' => 'f',
                ],
            ],
            'block' => [
                'id' => 'header',
                'template' => 't',
            ],
        ]));
    }

    public function testExistingVariablesArePreserved(): void
    {
        $this->assertSame([
            'header' => [
                'variables' => [
                    'foo' => 'bar',
                    'order' => '5',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'header',
            'order' => '5',
            'variables' => [
                'foo' => 'bar',
            ],
        ]));
    }

    public function testWithoutIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->element->normalize([
            'template' => 'x',
        ]);
    }
}
