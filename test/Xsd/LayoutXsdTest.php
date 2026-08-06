<?php

declare(strict_types=1);

namespace ConLayoutTest\Xsd;

use DOMDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LayoutXsdTest extends TestCase
{
    private const XSD_PATH = __DIR__ . '/../../xsd/layout.xsd';

    /**
     * @return iterable<string, array{string}>
     */
    public static function typedFixtureProvider(): iterable
    {
        $directory = dirname(__DIR__) . '/Updater/Collector/_files/typed';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && 'xml' === $file->getExtension()) {
                yield $file->getPathname() => [$file->getPathname()];
            }
        }
    }

    #[DataProvider('typedFixtureProvider')]
    public function testTypedFixtureValidates(string $file): void
    {
        $document = new DOMDocument();
        $this->assertTrue($document->load($file));
        $this->assertTrue($this->validate($document), sprintf('Failed validating %s', $file));
    }

    public function testLegacyContainersAreRejected(): void
    {
        $document = new DOMDocument();
        $document->loadXML('<blocks><header/></blocks>');
        $this->assertFalse($this->validate($document));
    }

    private function validate(DOMDocument $document): bool
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            return $document->schemaValidate(self::XSD_PATH);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
