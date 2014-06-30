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
        $sm = $mvcEvent->getApplication()->getServiceManager();
        $config = $sm->get('ConLayout\Service\Config');
        $blocksBuilder = $sm->get('ConLayout\Service\BlocksBuilder');
        $data = array(
            'handles' => $config->getHandles(),
            'layoutConfig' => $config->getLayoutConfig()->toArray(),
            'blocks' => array()
        );
        foreach ($blocksBuilder->getBlocks() as $name => $instance) {
            $data['blocks'][$name] = get_class($instance);
        }
        $this->data = $data;
    }
    
    public function getHandles()
    {
        return $this->data['handles'];
    }
    
    public function getLayoutConfig()
    {
        return $this->data['layoutConfig'];
    }
    
    public function getBlocks()
    {
        return $this->data['blocks'];
    }
}
