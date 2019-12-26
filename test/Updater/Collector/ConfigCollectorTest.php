<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayoutTest\Updater\Collector;

use ConLayout\Updater\Collector\CollectorInterface;
use ConLayout\Updater\Collector\ConfigCollector;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConLayoutTest\AbstractTest;
use Zend\Config\Config;

class ConfigCollectorTest extends AbstractTest
{
    /**
     * @var CollectorInterface
     */
    protected $collector;

    protected function setUp(): void
    {
        parent::setUp();
        $config = [
            LayoutUpdaterInterface::AREA_GLOBAL => [
                'default' => [
                    'blocks' => [
                        'root' => [],
                        'global.block' => [
                            'template' => 'lorem/ipsum'
                        ]
                    ]
                ]
            ],
            'frontend' => [
                'default' => [
                    'blocks' => [
                        'my.block' => [],
                        'global.block' => [
                            'template' => 'tpl/for/frontend'
                        ]
                    ]
                ]
            ],
            'backend' => [
                'app/index' => [
                    'blocks' => [
                        'some.block' => []
                    ]
                ]
            ]
        ];
        $this->collector = new ConfigCollector($config);
    }

    public function testCollectorCollectsHandle()
    {
        $result = $this->collector->collect('default', 'frontend');
        $this->assertInstanceOf(Config::class, $result);
        $this->assertNotEmpty($result->get('blocks'));
    }

    public function testAreaOverridesGlobal()
    {
        $result = $this->collector->collect('default', 'frontend');

        $globalBlock = $result->blocks->get('global.block');
        $this->assertInstanceOf(Config::class, $globalBlock);
        $this->assertEquals(
            'tpl/for/frontend',
            $globalBlock->template
        );
    }
}
