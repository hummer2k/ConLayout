<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector\Element;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Updater\Collector\Element\TitleElement;
use PHPUnit\Framework\TestCase;

class TitleElementTest extends TestCase
{
    private TitleElement $element;

    protected function setUp(): void
    {
        $this->element = new TitleElement();
    }

    public function testNameAndInstruction(): void
    {
        $this->assertSame('title', $this->element->name());
        $this->assertSame('helpers', $this->element->instruction());
    }

    public function testContentIsMappedToValueWithAppendAsDefault(): void
    {
        $this->assertSame([
            'headTitle' => [
                'default' => [
                    'value' => 'TEST',
                    'method' => 'append',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'default',
            '_' => 'TEST',
        ]));
    }

    public function testContentIsTrimmed(): void
    {
        $this->assertSame([
            'headTitle' => [
                'default' => [
                    'value' => 'TEST',
                    'method' => 'append',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'default',
            '_' => " \n TEST \n ",
        ]));
    }

    public function testSetAndPrependSelectTheMethod(): void
    {
        $this->assertSame([
            'headTitle' => [
                'site' => [
                    'value' => 'My Shop',
                    'method' => 'set',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'site',
            'set' => 'true',
            '_' => 'My Shop',
        ]));
        $this->assertSame([
            'headTitle' => [
                'home' => [
                    'value' => 'Home',
                    'method' => 'prepend',
                ],
            ],
        ], $this->element->normalize([
            'id' => 'home',
            'prepend' => 'true',
            '_' => 'Home',
        ]));
    }

    public function testWithoutContentThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->element->normalize([
            'id' => 'products',
        ]);
    }

    public function testWithMultiplePlacementsThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->element->normalize([
            'id' => 'products',
            'set' => 'true',
            'prepend' => 'true',
            '_' => 'Products',
        ]);
    }
}
