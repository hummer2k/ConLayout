<?php
namespace ConLayout\Service\Config;

use Zend\Config\Config as ZendConfig;
/**
 * Collector
 *
 * @author hummer 
 */
class Collector
    implements CollectorInterface
{
    /**
     *
     * @var string
     */
    protected $globPath;
    
    /**
     *
     * @var array
     */
    protected $layoutConfigs = array();
    
    /**
     * 
     * @param string $globPath
     */
    public function __construct($globPath)
    {
        $this->globPath = $globPath;
    }
    
    /**
     * 
     * @return array
     */
    public function collect()
    {
        if (empty($this->layoutConfigs)) {
            $configFiles = glob($this->globPath, GLOB_BRACE);
            foreach ($configFiles as $configFile) {
                if (is_readable($configFile)) {
                    $this->layoutConfigs[] = new ZendConfig(
                        include $configFile, true
                    );
                }
            }
        }
        return $this->layoutConfigs;
    }
}
