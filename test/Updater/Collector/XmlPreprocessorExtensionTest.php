<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector;

use ConLayout\Updater\Collector\Element\ElementInterface;
use ConLayout\Updater\Collector\XmlPreprocessor;
use Laminas\Config\Config;
use PHPUnit\Framework\TestCase;

class XmlPreprocessorExtensionTest extends TestCase
{
    public function testCustomElementIsAppliedAndItsKeyIsRemoved(): void
    {
        $preprocessor = new XmlPreprocessor([new FakeLinkElement()]);
        $config = new Config([
            'link' => [
                'id' => 'docs',
                'href' => '/docs',
            ],
            'blocks' => [
                'legacy' => [
                    'template' => 't',
                ],
            ],
        ], true);

        $this->assertSame([
            'blocks' => [
                'legacy' => [
                    'template' => 't',
                ],
            ],
            'helpers' => [
                'headLink' => [
                    'docs' => [
                        'href' => '/docs',
                    ],
                ],
            ],
        ], $preprocessor->process($config)->toArray());
    }

    public function testNoElementsPassesInputThroughUnchanged(): void
    {
        $preprocessor = new XmlPreprocessor([]);
        $config = new Config([
            'blocks' => [
                'legacy' => [
                    'template' => 't',
                ],
            ],
        ], true);

        $this->assertSame($config->toArray(), $preprocessor->process($config)->toArray());
    }
}

class FakeLinkElement implements ElementInterface
{
    public function name(): string
    {
        return 'link';
    }

    public function instruction(): string
    {
        return 'helpers';
    }

    public function normalize(array $spec): array
    {
        $id = $spec['id'];
        unset($spec['id']);
        return ['headLink' => [$id => $spec]];
    }
}
