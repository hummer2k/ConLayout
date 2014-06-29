<?php
namespace ConLayout;

use Zend\EventManager\EventInterface as Event;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return include __DIR__ . '/config/service.config.php';
    }
    
    /**
     * 
     * @param \Zend\EventManager\EventInterface $e
     */
    public function onBootstrap(Event $e)
    {
        $application  = $e->getApplication();
        $services     = $application->getServiceManager();        
        $eventManager = $application->getEventManager();
        
        $eventManager->attach('render', function($e) use ($services) {
            $layoutModifier = $services->get('ConLayout\Service\LayoutModifier');
            $layoutModifier->addBlocksToLayout();
        }); 
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
