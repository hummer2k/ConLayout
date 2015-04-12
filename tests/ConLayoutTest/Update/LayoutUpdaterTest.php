<?php

namespace ConLayoutTest;

use ConLayout\Handle\Handle;
use ConLayout\Updater\Event\UpdateEvent;
use ConLayout\Updater\LayoutUpdater;
use Zend\Cache\StorageFactory;
use Zend\Config\Config;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdaterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddHandle()
    {
        $updater = new LayoutUpdater();
        $this->assertSame([
            'default'
        ], $updater->getHandles());

        $updater->addHandle(new Handle('my-handle', 5));

        $this->assertSame([
            'default',
            'my-handle'
        ], $updater->getHandles());

        $updater->addHandle(new Handle('test-handle', 2));

        $this->assertSame([
            'default',
            'test-handle',
            'my-handle'
        ], $updater->getHandles());
    }

    public function testGetHandlesAsObject()
    {
        $updater = new LayoutUpdater();
        $updater->addHandle(new Handle('my-handle', 2));

        $handles = $updater->getHandles(true);

        foreach ($handles as $handle) {
            $this->assertInstanceOf('ConLayout\Handle\Handle', $handle);
            $this->assertInstanceOf('ConLayout\Handle\HandleInterface', $handle);
        }
    }

    public function testApplyForHandles()
    {
        $eventManager = new EventManager();
        $updater = new LayoutUpdater();

        $newGlobalLayoutStructure = $this->getGlobalLayoutStructure();
        $newGlobalLayoutStructure->merge(new Config([
            'my-handle' => [
                'apply_for' => [
                    'test-handle' => true
                ],
                'blocks' => [
                    'my.widget' => []
                ]
            ],
            'test-handle' => [
            ]
        ]));

         $eventManager->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            function(UpdateEvent $e) use ($newGlobalLayoutStructure) {
                $globalLayoutStructure = $e->getGlobalLayoutStructure();
                $globalLayoutStructure->merge($newGlobalLayoutStructure);
            }
        );

        $updater->addHandle(new Handle('test-handle', 5));

        $layoutStructure = $updater->getLayoutStructure();

        $expected = [
            'my-block' => [
                'capture_to' => 'sidebarLeft'
            ],
            'my.widget' => []
        ];

        $this->assertSame($expected, $layoutStructure->get('blocks')->toArray());

    }

    public function testRemoveHandle()
    {
        $updater = new LayoutUpdater();
        $updater->addHandle(new Handle('test-handle', 2));

        $this->assertSame([
            'default',
            'test-handle'
        ], $updater->getHandles());

        $updater->removeHandle('test-handle');

        $this->assertSame([
            'default'
        ], $updater->getHandles());
    }

    protected function attachGlobalLayoutStructureListener(
        EventManager $eventManager
    )
    {
        $eventManager->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            function(UpdateEvent $e) {
                $globalLayoutStructure = $e->getGlobalLayoutStructure();
                $globalLayoutStructure->merge($this->getGlobalLayoutStructure());
            }
        );
    }

    protected function getGlobalLayoutStructure()
    {
        return new Config([
            'default' => [
                'blocks' => [
                    'my-block' => [
                        'capture_to' => 'sidebarLeft'
                    ]
                ]
            ]
        ], true);
    }

    public function testGetLayoutStructure()
    {
        $eventManager = new EventManager();
        $this->attachGlobalLayoutStructureListener($eventManager);

        $updater = new LayoutUpdater();
        $updater->setEventManager($eventManager);

        $layoutStructure = $updater->getLayoutStructure();

        $this->assertEquals([
            'blocks' => [
                'my-block' => [
                    'capture_to' => 'sidebarLeft'
                ]
            ]
        ], $layoutStructure->toArray());
    }

    public function testGetLayoutStructureWithCache()
    {
        $eventManager = new EventManager();
        $this->attachGlobalLayoutStructureListener($eventManager);

        $layoutCacheListener = new LayoutCacheListener();
        $layoutCacheListener->attach($eventManager);

        $updater = new LayoutUpdater();
        $updater->setEventManager($eventManager);

        $layoutStructure = $updater->getLayoutStructure();

        $this->assertEquals([
            'blocks' => [
                'my-block' => [
                    'capture_to' => 'sidebarLeft'
                ]
            ]
        ], $layoutStructure->toArray());


        $updater2 = new LayoutUpdater();
        $updater2->setEventManager($eventManager);

        $layoutStructure = $updater2->getLayoutStructure();

        $this->assertEquals([
            'blocks' => [
                'my-block' => [
                    'capture_to' => 'sidebarLeft'
                ]
            ],
            'cached' => true
        ], $layoutStructure->toArray());

    }
}

class LayoutCacheListener implements ListenerAggregateInterface
{
    use \Zend\EventManager\ListenerAggregateTrait;

    const CACHE_KEY = 'global-layout-structure';

    protected $cache;
    
    public function __construct()
    {
        $this->cache = StorageFactory::factory([
            'adapter' => [
                'name' => 'memory'
            ],
            'plugins' => [
                'exception_handler' => [
                    'throw_exceptions' => false
                ],
                'Serializer'
            ]
        ]);
    }

    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.pre',
            array($this, 'loadCache'),
            100
        );
        $events->getSharedManager()->attach(
            'ConLayout\Updater\LayoutUpdater',
            'loadGlobalLayoutStructure.post',
            array($this, 'saveCache'),
            -100
        );
    }

    public function loadCache(UpdateEvent $e)
    {
        if ($this->cache->hasItem(self::CACHE_KEY)) {
            $cachedGLobalLayoutConfig = new Config(
                $this->cache->getItem(self::CACHE_KEY), true
            );
            return $cachedGLobalLayoutConfig;
        }
    }

    public function saveCache(EventInterface $e)
    {
        /* @var $result Config */
        $result = $e->getParam('__RESULT__');
        $aResult = $result->toArray();
        $aResult['default']['cached'] = true;
        $this->cache->setItem(self::CACHE_KEY, $aResult);
    }
}
