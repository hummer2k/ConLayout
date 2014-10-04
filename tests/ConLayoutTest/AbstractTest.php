<?php
namespace ConLayoutTest;

use ConLayout\Service\BlocksBuilder;
/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var array
     */
    protected $config;
    
    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;
    
    /**
     *
     * @var \ConLayout\Service\Config\CollectorInterface
     */
    protected $collector;
    
    /**
     *
     * @var Cache
     */
    protected $cache;
    
    /**
     *
     * @var \ConLayout\Service\Config\SorterInterface
     */
    protected $sorter;
    
    /**
     *
     * @var \ConLayout\Service\LayoutService
     */
    protected $layoutService;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->config = $this->sm->get('Config'); 
        
        $this->collector = new \ConLayout\Service\Config\Collector(array(
            './module/ConLayout/tests/config/layout.*.php'
        ));
        $this->sorter = new \ConLayout\Service\Config\Sorter(array(
            'default'   => -20,
            '\\'        => 0,
            '/'         => function($handle, $substr) {
                return substr_count($handle, $substr);
            },
            '::'        => 10
        ));
        $cacheDir = './data/cache/con-layout-test';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $this->cache =  \Zend\Cache\StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array(
                    'cache_dir' => $cacheDir
                )
            ),
            'plugins' => array(
                'serializer',
                'exception_handler' => array(
                    'throw_exceptions' => false
                ),
            )
        ));
        $this->layoutService = new \ConLayout\Service\LayoutService(
            $this->collector,
            $this->cache,
            $this->sorter
        );
        $this->layoutService->setIsCacheEnabled(false);
    }
    
    /**
     * 
     * @return \ConLayoutTest\BlocksBuilder
     */
    protected function getBlocksBuilder()
    {
        $blocksBuilder = new BlocksBuilder($this->layoutService->getBlockConfig());
        $blocksBuilder->setServiceLocator($this->sm);
        return $blocksBuilder;
    }
}
