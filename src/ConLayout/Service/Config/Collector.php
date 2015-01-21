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
     * @var string
     */
    protected $area;
    
    /**
     * 
     * @param array $globPaths
     */
    public function __construct(array $globPaths, $area = null)
    {
        $this->globPaths = $globPaths;
        $this->setArea($area);
    }

    /**
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     *
     * @param string $area
     * @return \ConLayout\Service\Config\Collector
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function collect()
    {
        if (empty($this->layoutConfigs)) {
            foreach ($this->globPaths as $globPath) {
                if (null !== $this->area) {
                    $globPath = str_replace('{{area}}', $this->area, $globPath);
                }
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
