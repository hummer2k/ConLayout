<?php
namespace ConLayout\Collector;

use ZendDeveloperTools\Collector\AbstractCollector;
/**
 * Collector for ZendDeveloperToolbar
 *
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutCollector
    extends AbstractCollector
{
    const NAME = 'con-layout';
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * 
     * @return int
     */
    public function getPriority()
    {
        return 600;
    }
    
    /**
     * collect data for zdt
     * 
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     * @return \ConLayout\Collector\LayoutCollector
     */
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
        return $this;
    }
    
    /**
     * 
     * @return array
     */
    public function getHandles()
    {
        return $this->data['handles'];
    }
    
    /**
     * 
     * @return array
     */
    public function getLayoutConfig()
    {
        return $this->data['layoutConfig'];
    }
    
    /**
     * 
     * @return array
     */
    public function getBlocks()
    {
        return $this->data['blocks'];
    }
}
