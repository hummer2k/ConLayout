<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector\Element;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Updater\Collector\Element\CssElement;
use PHPUnit\Framework\TestCase;

class CssElementTest extends TestCase
{
    private CssElement $element;

    protected function setUp(): void
    {
        $this->element = new CssElement();
    }

    public function testNameAndInstruction(): void
    {
        $this->assertSame('css', $this->element->name());
        $this->assertSame('helpers', $this->element->instruction());
    }

    public function testSrcIsMappedToHref(): void
    {
        $this->assertSame([
            'headLink' => [
                'all.css' => [
                    'href' => '/css/all.css',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'all.css',
            'src' => '/css/all.css',
        ]));
    }

    public function testHrefPassesThroughUntouched(): void
    {
        $this->assertSame([
            'headLink' => [
                'all.css' => [
                    'href' => 'https://example.org/all.css',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'all.css',
            'href' => 'https://example.org/all.css',
        ]));
    }

    public function testWithoutIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->element->normalize([
            'src' => '/css/all.css',
        ]);
    }
}
