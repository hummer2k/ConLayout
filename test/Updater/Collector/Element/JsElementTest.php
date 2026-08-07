<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector\Element;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Updater\Collector\Element\JsElement;
use PHPUnit\Framework\TestCase;

class JsElementTest extends TestCase
{
    private JsElement $element;

    protected function setUp(): void
    {
        $this->element = new JsElement();
    }

    public function testNameAndInstruction(): void
    {
        $this->assertSame('js', $this->element->name());
        $this->assertSame('helpers', $this->element->instruction());
    }

    public function testNormalizeMapsToHeadScript(): void
    {
        $this->assertSame([
            'headScript' => [
                'main.js' => [
                    'src' => '/js/main.js',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'main.js',
            'src' => '/js/main.js',
        ]));
    }

    public function testInlineTrueMapsToInlineScript(): void
    {
        $this->assertSame([
            'inlineScript' => [
                'main.js' => [
                    'src' => '/js/main.js',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'main.js',
            'src' => '/js/main.js',
            'inline' => 'true',
        ]));
    }

    public function testInlineFalseStaysOnHeadScript(): void
    {
        $this->assertSame([
            'headScript' => [
                'main.js' => [
                    'src' => '/js/main.js',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'main.js',
            'src' => '/js/main.js',
            'inline' => 'false',
        ]));
    }

    public function testWithoutIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->element->normalize([
            'src' => '/js/main.js',
        ]);
    }
}
