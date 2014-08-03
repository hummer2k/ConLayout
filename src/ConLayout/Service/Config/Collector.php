<?php
namespace ConLayout\Service\Config;

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
     * @param array $globPaths
     */
    public function __construct(array $globPaths)
    {
        $this->globPaths = $globPaths;
    }
    
    /**
     * 
     * @return array
     */
    public function collect()
    {
        if (empty($this->layoutConfigs)) {
            foreach ($this->globPaths as $globPath) {
                $configFiles = glob($globPath, GLOB_BRACE);
                foreach ($configFiles as $configFile) {
                    if (is_readable($configFile)) {
                        $this->layoutConfigs[] = include $configFile;
                    }
                }
            }
        }
        return $this->layoutConfigs;
    }
}
