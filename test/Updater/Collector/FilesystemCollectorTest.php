<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector;

use ConLayout\Updater\Collector\CollectorInterface;
use ConLayout\Updater\Collector\FilesystemCollector;
use ConLayout\Updater\Collector\XmlPreprocessor;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\AbstractTestCase;
use Laminas\Config\Config;

class FilesystemCollectorTest extends AbstractTestCase
{
    /**
     * @var CollectorInterface
     */
    protected $collector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collector = new FilesystemCollector([
            LayoutUpdaterInterface::AREA_GLOBAL => [
                __DIR__ . '/_files/global',
            ],
            'frontend' => [
                __DIR__ . '/_files/frontend',
            ],
        ]);
    }

    public function testCollectorCollectsHandle(): void
    {
        $result = $this->collector->collect('default');
        $this->assertInstanceOf(Config::class, $result);
        $this->assertNotEmpty($result->get('blocks'));
        $block = $result->blocks->get('some.block');
        $this->assertEmpty($block->get('template'));
    }

    public function testAreaOverridesGlobal(): void
    {
        $result = $this->collector->collect('default', 'frontend');
        $block = $result->blocks->get('some.block');
        $this->assertEquals(
            'tpl/for/frontend',
            $block->get('template'),
        );
    }

    public function testSameBasePathForGlobalAndAreaCollectsFileOnce(): void
    {
        $path = __DIR__ . '/_files/global';
        $collector = new FilesystemCollector([
            LayoutUpdaterInterface::AREA_GLOBAL => [$path],
            'frontend' => [$path],
        ]);
        $collector->collect('default', 'frontend');
        $this->assertSame(
            [$path . '/default.php'],
            $collector->getCollectedFiles()['default'],
        );
    }

    public function testTypedXmlIsNormalized(): void
    {
        $collector = new FilesystemCollector(
            [
                LayoutUpdaterInterface::AREA_GLOBAL => [
                    __DIR__ . '/_files/typed/global',
                ],
            ],
            ['xml'],
            new XmlPreprocessor(),
        );
        $result = $collector->collect('default');
        $this->assertInstanceOf(Config::class, $result);
        $this->assertSame('tpl/header', $result->blocks->get('header')->get('template'));
        $this->assertSame(
            ['order' => '1'],
            $result->blocks->get('header')->variables->toArray(),
        );
        $this->assertSame('tpl/footer', $result->blocks->get('footer')->get('template'));
    }
}
