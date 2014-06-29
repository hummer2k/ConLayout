<?php
namespace ConLayout\Collector;

use ZendDeveloperTools\Collector\AbstractCollector;
/**
 * LayoutCollector
 *
 * @author hummer 
 */
class LayoutCollector
    extends AbstractCollector
{
    const NAME = 'con-layout';
    
    public function getName()
    {
        return self::NAME;
    }
    
    public function getPriority()
    {
        return 600;
    }
    
    public function collect(\Zend\Mvc\MvcEvent $mvcEvent)
    {
        $config = $mvcEvent->getApplication()
            ->getServiceManager()
            ->get('ConLayout\Service\Config');
        $data = array(
            'blockConfig' => $config->getBlockConfig(),
            'handles' => $config->getHandles(),
            'layoutConfig' => $config->getLayoutConfig()
        );
        $this->data = $data;
    }
        
    public function getBlockConfig()
    {
        return $this->data['blockConfig'];
    }
    
    public function getHandles()
    {
        return $this->data['handles'];
    }
    
    public function getLayoutConfig()
    {
        return $this->data['layoutConfig'];
    }
    
}
