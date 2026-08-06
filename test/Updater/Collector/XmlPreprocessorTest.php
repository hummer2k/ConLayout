<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector;

use ConLayout\Exception\InvalidArgumentException;
use ConLayout\Updater\Collector\XmlPreprocessor;
use Laminas\Config\Config;
use Laminas\Config\Reader\Xml;
use PHPUnit\Framework\TestCase;

class XmlPreprocessorTest extends TestCase
{
    private XmlPreprocessor $preprocessor;

    private Xml $reader;

    protected function setUp(): void
    {
        $this->preprocessor = new XmlPreprocessor();
        $this->reader = new Xml();
    }

    private function process(string $xml): array
    {
        $config = new Config($this->reader->fromString($xml), true);
        return $this->preprocessor->process($config)->toArray();
    }

    public function testTypedBlockIsMappedIntoBlocks(): void
    {
        $this->assertSame([
            'blocks' => [
                'header' => [
                    'template' => 'app::layout/header',
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <block id="header" template="app::layout/header" />
        </page>
        XML));
    }

    public function testMultipleTypedBlocksAreMapped(): void
    {
        $this->assertSame([
            'blocks' => [
                'header' => [
                    'template' => 'app::layout/header',
                ],
                'footer' => [
                    'template' => 'app::layout/footer',
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <block id="header" template="app::layout/header" />
            <block id="footer" template="app::layout/footer" />
        </page>
        XML));
    }

    public function testNestedTypedBlocksAreMappedRecursively(): void
    {
        $this->assertSame([
            'blocks' => [
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
                        'main.content' => [
                            'template' => 'm',
                        ],
                        'footer' => [
                            'template' => 'f',
                        ],
                    ],
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <block id="root" template="base">
                <block id="header" template="h">
                    <block id="main.nav" template="n" />
                </block>
                <block id="main.content" template="m" />
                <block id="footer" template="f" />
            </block>
        </page>
        XML));
    }

    public function testInsertionMetadataIsMovedIntoVariables(): void
    {
        $this->assertSame([
            'blocks' => [
                'header' => [
                    'template' => 't',
                    'variables' => [
                        'order' => '1',
                        'parent' => 'root',
                    ],
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <block id="header" order="1" parent="root" template="t" />
        </page>
        XML));
    }

    public function testExistingVariablesAndActionsArePreserved(): void
    {
        $this->assertSame([
            'blocks' => [
                'header' => [
                    'actions' => [
                        'a' => [
                            'method' => 'm',
                        ],
                    ],
                    'variables' => [
                        'foo' => 'bar',
                        'order' => '5',
                    ],
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <block id="header" order="5">
                <variables>
                    <foo>bar</foo>
                </variables>
                <actions>
                    <a method="m" />
                </actions>
            </block>
        </page>
        XML));
    }

    public function testTypedAndLegacyBlocksMixInOneFile(): void
    {
        $this->assertSame([
            'blocks' => [
                'footer' => [
                    'template' => 'f',
                ],
                'header' => [
                    'template' => 't',
                    'variables' => [
                        'order' => '1',
                    ],
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <blocks>
                <footer template="f" />
            </blocks>
            <block id="header" order="1" template="t" />
        </page>
        XML));
    }

    public function testTypedViewHelperIsMappedIntoHelpers(): void
    {
        $this->assertSame([
            'helpers' => [
                'headScript' => [
                    'main' => [
                        'src' => '/js/main.js',
                        'depends' => 'jquery',
                    ],
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <view_helper name="headScript" id="main" src="/js/main.js" depends="jquery" />
        </page>
        XML));
    }

    public function testTypedReferenceIsMappedById(): void
    {
        $this->assertSame([
            'reference' => [
                'main.content' => [
                    'append' => 'true',
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <reference id="main.content" append="true" />
        </page>
        XML));
    }

    public function testLegacyReferenceContainerIsUntouched(): void
    {
        $this->assertSame([
            'reference' => [
                'main.content' => [
                    'append' => 'true',
                ],
            ],
        ], $this->process(<<<XML
        <page>
            <reference>
                <main.content append="true" />
            </reference>
        </page>
        XML));
    }

    public function testLegacyLayoutIsUnchanged(): void
    {
        $this->assertSame([
            'blocks' => [
                'root' => [
                    'blocks' => [
                        'header' => [
                            'template' => 'layout::header',
                            'capture_to' => 'header',
                        ],
                    ],
                    'template' => 'base',
                ],
            ],
        ], $this->process(<<<XML
        <layout>
            <blocks>
                <root template="base">
                    <blocks>
                        <header template="layout::header" capture_to="header" />
                    </blocks>
                </root>
            </blocks>
        </layout>
        XML));
    }

    public function testBlockWithoutIdThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->process(<<<XML
        <page>
            <block template="x" />
        </page>
        XML);
    }

    public function testViewHelperWithoutNameThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->process(<<<XML
        <page>
            <view_helper id="main" src="/js/main.js" />
        </page>
        XML);
    }
}
