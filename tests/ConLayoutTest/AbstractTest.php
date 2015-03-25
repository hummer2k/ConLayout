<?php
namespace ConLayoutTest;

use ConLayout\Service\BlocksBuilder;
use ConLayout\Service\Config\Collector;
use ConLayout\Service\Config\CollectorInterface;
use ConLayout\Service\Config\Sorter;
use ConLayout\Service\Config\SorterInterface;
use ConLayout\Service\LayoutService;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\XmlRpc\Server\Cache;
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
     * @var ServiceManager
     */
    protected $sm;
    
    /**
     *
     * @var CollectorInterface
     */
    protected $collector;
    
    /**
     *
     * @var Cache
     */
    protected $cache;
    
    /**
     *
     * @var SorterInterface
     */
    protected $sorter;
    
    /**
     *
     * @var LayoutService
     */
    protected $layoutService;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->config = $this->sm->get('Config'); 
        
        $this->collector = new Collector(array(
            __DIR__ . '/_config/layout.*.php'
        ));
        $this->collector->collect();
        $this->sorter = new Sorter(
            $this->config['con-layout']['sorter']['priorities']
        );
        $cacheDir = './data/cache/con-layout-test';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $this->cache =  StorageFactory::factory(array(
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
        $this->layoutService = new LayoutService(
            $this->collector,
            $this->cache,
            $this->sorter
        );
        $this->layoutService->getGlobalLayoutConfig();
        $this->layoutService->getBlockConfig();
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
