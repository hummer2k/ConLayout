<?php
namespace ConLayoutTest;

use ConLayout\Service\BlocksBuilder;
use ConLayout\Config\Collector;
use ConLayout\Config\CollectorInterface;
use ConLayout\Config\Sorter;
use ConLayout\Config\SorterInterface;
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
    use \ConLayout\OptionTrait;
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
        $cacheDir = __DIR__ . '/_files/cache';
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
        foreach ($this->getOption($this->config, 'con-layout/block_config_mutators', array()) as $blockConfigModifier) {
            $this->layoutService->addBlockConfigMutator($this->sm->get($blockConfigModifier));
        }
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
