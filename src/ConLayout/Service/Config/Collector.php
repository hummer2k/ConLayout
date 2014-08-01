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
     * @var array
     */
    protected $globPaths;
    
    /**
     *
     * @var array
     */
    protected $layoutConfigs = array();
    
    /**
     * 
     * @param string $globPath
     */
    public function __construct(array $globPaths)
    {
        $this->globPaths = $globPaths;
    }
    
    /**
     * 
     * @return array collection of ZendConfig instances
     */
    public function collect()
    {
        if (empty($this->layoutConfigs)) {
            foreach ($this->globPaths as $globPath) {
                $configFiles = glob($globPath, GLOB_BRACE);
                foreach ($configFiles as $configFile) {
                    if (is_readable($configFile)) {
                        $this->layoutConfigs[] = new ZendConfig(
                            include $configFile, true
                        );
                    }
                }
            }
        }
        return $this->layoutConfigs;
    }
}
