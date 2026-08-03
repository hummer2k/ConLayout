<?php

declare(strict_types=1);

namespace ConLayoutTest\Updater\Collector;

use ConLayout\Updater\Collector\CollectorInterface;
use ConLayout\Updater\Collector\FilesystemCollector;
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
                __DIR__ . '/_files/global'
            ],
            'frontend' => [
                __DIR__ . '/_files/frontend'
            ]
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
            $block->get('template')
        );
    }
}
