<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector;

use ConLayout\Options\ModuleOptions;
use ConLayout\Updater\Collector\Element\BlockElement;
use ConLayout\Updater\Collector\Element\ReferenceElement;
use ConLayout\Updater\Collector\XmlPreprocessor;
use ConLayout\Updater\Collector\XmlPreprocessorFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class XmlPreprocessorFactoryTest extends TestCase
{
    public function testFactoryBuildsPreprocessorFromModuleOptions(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $options = new ModuleOptions([
            'xml_preprocessor' => [
                'elements' => [
                    'block' => BlockElement::class,
                    'reference' => ReferenceElement::class,
                ],
            ],
        ]);
        $container->method('get')
            ->willReturnMap([
                [ModuleOptions::class, $options],
                [BlockElement::class, new BlockElement()],
                [ReferenceElement::class, new ReferenceElement()],
            ]);

        $preprocessor = (new XmlPreprocessorFactory())($container, XmlPreprocessor::class);

        $this->assertInstanceOf(XmlPreprocessor::class, $preprocessor);
    }

    public function testFactoryRejectsNonElementService(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $options = new ModuleOptions([
            'xml_preprocessor' => [
                'elements' => [
                    'block' => BlockElement::class,
                ],
            ],
        ]);
        $container->method('get')
            ->willReturnMap([
                [ModuleOptions::class, $options],
                [BlockElement::class, new stdClass()],
            ]);

        $this->expectException(\InvalidArgumentException::class);
        (new XmlPreprocessorFactory())($container, XmlPreprocessor::class);
    }

    public function testFactoryWithoutElementsConfigBuildsEmptyPreprocessor(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $options = new ModuleOptions([]);
        $container->method('get')
            ->with(ModuleOptions::class)
            ->willReturn($options);

        $preprocessor = (new XmlPreprocessorFactory())($container, XmlPreprocessor::class);

        $this->assertInstanceOf(XmlPreprocessor::class, $preprocessor);
    }
}
