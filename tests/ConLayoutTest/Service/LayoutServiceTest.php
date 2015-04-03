<?php
namespace ConLayoutTest\Service;

use ConLayout\Config\Collector;
use ConLayout\Config\Sorter;
use ConLayout\Service\LayoutService;
use ConLayout\Service\LayoutServiceFactory;
use ConLayoutTest\AbstractTest;
use Zend\Cache\StorageFactory;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutServiceTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new LayoutServiceFactory();
        $this->assertInstanceOf(
            'ConLayout\Service\LayoutService',
            $factory->createService($this->sm)
        );
    }

    public function testAddHandle()
    {
        $this->layoutService->addHandle('route');
        $this->assertEquals(array(
            'default',
            'route'
        ), $this->layoutService->getHandles());
        
        $this->layoutService->addHandle(array(
            'route/childroute',
            'controller::action'
        ));
        
        $this->assertEquals(array(
            'default', 'route', 'route/childroute', 'controller::action'
        ), $this->layoutService->getHandles());

        $this->layoutService->removeHandle('route/childroute');
        $this->assertEquals(array(
            'default', 'route', 'controller::action'
        ), $this->layoutService->getHandles());

        $this->layoutService->removeHandle(['route', 'controller::action']);
        $this->assertEquals(['default'], $this->layoutService->getHandles());
    }

    public function testLayoutTemplate()
    {
        $this->layoutService->reset();
        $this->assertEquals('layout/2cols-left', $this->layoutService->getLayoutTemplate());
        
        $this->layoutService->reset();
        $this->layoutService->addHandle('route/childroute');
        $this->assertEquals('layout/2cols-right', $this->layoutService->getLayoutTemplate());
    
        $this->layoutService->reset();
        $this->layoutService->addHandle(array(
            'route/childroute',
            'controller::action'
        ));
        $this->assertEquals('layout/1col', $this->layoutService->getLayoutTemplate());
    }
    
    public function testRemoveBlocks()
    {
        $this->layoutService->reset();
        $blockConfig = $this->layoutService->getBlockConfig();
        
        $this->assertArrayHasKey('block.header', $blockConfig['header']);
        
        $this->layoutService->reset();        
        $this->layoutService->addHandle('remove-handle');
        $blockConfig = $this->layoutService->getBlockConfig();
        
        $this->assertFalse(isset($blockConfig['header']['block.header']));
        
    }
    
    public function testGetBlockConfig()
    {
        $blockConfig = $this->layoutService->getBlockConfig();
        $this->assertEquals($blockConfig, array(
            'header' => array(
                'block.header' => array(
                    'class' => 'ConLayout\Block\Dummy'
                )
            )
        ));
    }
    
    public function testGetGlobalLayoutConfig()
    {
        $globalLayoutConfig = $this->layoutService->reset()->getGlobalLayoutConfig();
        $this->assertSame($globalLayoutConfig, array(
            'default' => array(
                'layout' => 'layout/2cols-left',
                'handles' => [
                    'my-custom-handle'
                ],
                'blocks' => array(
                    'header' => array(
                        'block.header' => array(
                            'class' => 'ConLayout\Block\Dummy'
                        )
                    )
                )
            ),
            'route' => array(
                'blocks' => array(
                    'sidebar.right' => array(
                        'block.sidebar.right' => array(
                            'class' => 'ConLayout\Block\Dummy',
                            'children' => array(
                                'childCapture' => array(
                                    'block.sidebar.right.child1' => array(
                                        'class' => 'ConLayout\Block\Dummy'
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            'remove-handle' => array(
                'blocks' => array(
                    '_remove' => array(
                        'block.header' => true
                    )
                )
            ), 
            'route/childroute' => array(
                'layout' => 'layout/2cols-right',
                'blocks' => array(
                    'sidebar' => array(
                        'block.sidebar' => array(
                            'class' => 'ConLayout\Block\Dummy'
                        ),
                        'block.sidebar.before' => array(
                            'template' => 'lorem/ipsum',
                            'options' => array(
                                'order' => -10
                            )
                        )
                    )
                )
            ),
            'controller::action' => array(
                'layout' => 'layout/1col'
            ),
        ));
    }

    public function testCache()
    {
        $layoutService = $this->getLayoutService();

        $layoutService->setIsCacheEnabled(true);

        $layoutService->setLayoutConfig([
            'blocks' => [
                'header' => [
                    'my.header' => []
                ]
            ]
        ]);

        $expectedBlockConfig = [
            'header' => [
                'my.header' => []
            ]
        ];

        $this->assertEquals($expectedBlockConfig, $layoutService->getBlockConfig());

        $newLayoutConfig = [
            'blocks' => [
                'header' => [
                    'my.header' => [],
                    'my.second.header' => []
                ],
                'sidebar' => [
                    'my.sidebar.widget' => []
                ]
            ]
        ];
        $layoutService->setLayoutConfig($newLayoutConfig);

        $this->assertEquals($expectedBlockConfig, $layoutService->getBlockConfig());

        $this->assertTrue($layoutService->isCacheEnabled());
        $layoutService->setIsCacheEnabled(false);
        $this->assertFalse($layoutService->isCacheEnabled());

        $expectedBlockConfig = [
            'header' => [
                'my.header' => [],
                'my.second.header' => []
            ],
            'sidebar' => [
                'my.sidebar.widget' => []
            ]
        ];

        $this->assertEquals($expectedBlockConfig, $layoutService->getBlockConfig());
    }
    
    public function testGetLayoutTemplate()
    {
        $layoutService = $this->getLayoutService();
        $layoutService->setLayoutConfig([]);
        
        $this->assertNull($layoutService->getLayoutTemplate());

        $layoutService->setLayoutConfig([
            'layout' => '1col'
        ]);

        $this->assertEquals('1col', $layoutService->getLayoutTemplate());
    }

    public function testEmptyBlockConfig()
    {
        $layoutService = $this->getLayoutService();
        $layoutService->setLayoutConfig([]);

        $this->assertEquals([], $layoutService->getBlockConfig());
    }

    protected function getLayoutService()
    {
        $cache = StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'memory',
            ),
            'plugins' => array(
                'serializer',
                'exception_handler' => array(
                    'throw_exceptions' => false
                ),
            )
        ));
        $layoutService = new LayoutService(
            new Collector(array()),
            $cache,
            new Sorter(array())
        );
        return $layoutService;
    }
}
