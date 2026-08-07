<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector\Element;

use ConLayout\Updater\Collector\Element\ReferenceElement;
use PHPUnit\Framework\TestCase;

class ReferenceElementTest extends TestCase
{
    private ReferenceElement $element;

    protected function setUp(): void
    {
        $this->element = new ReferenceElement();
    }

    public function testNameAndInstruction(): void
    {
        $this->assertSame('reference', $this->element->name());
        $this->assertSame('reference', $this->element->instruction());
    }

    public function testTypedReferenceIsMappedById(): void
    {
        $this->assertSame([
            'main.content' => [
                'append' => 'true',
            ],
        ], $this->element->normalize([
            'id' => 'main.content',
            'append' => 'true',
        ]));
    }

    public function testTypedReferenceMovesMetadataIntoVariables(): void
    {
        $this->assertSame([
            'main.content' => [
                'append' => 'true',
                'variables' => [
                    'order' => '1',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'main.content',
            'append' => 'true',
            'order' => '1',
        ]));
    }

    public function testLegacyContainerEntryPassesThrough(): void
    {
        $this->assertSame([
            'main.content' => [
                'append' => 'true',
            ],
        ], $this->element->normalize([
            'main.content' => [
                'append' => 'true',
            ],
        ]));
    }
}
