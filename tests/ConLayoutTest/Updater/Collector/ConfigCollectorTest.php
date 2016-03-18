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

    public function setUp()
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
        $this->collector->setArea('frontend');
        $result = $this->collector->collect('default');
        $this->assertInstanceOf(Config::class, $result);
        $this->assertNotEmpty($result->get('blocks'));
    }

    public function testAreaOverridesGlobal()
    {
        $this->collector->setArea('frontend');
        $result = $this->collector->collect('default');

        $globalBlock = $result->blocks->get('global.block');
        $this->assertInstanceOf(Config::class, $globalBlock);
        $this->assertEquals(
            'tpl/for/frontend',
            $globalBlock->template
        );
    }
}
