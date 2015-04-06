<?php
namespace ConLayout;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventInterface as Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\MvcEvent;

/**
 * ConLayout\Module
 * 
 * 
 */
class Module
{
    /**
     * retrieve module config
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
    
    /**
     * retrieve services
     * 
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/service.config.php';
    }
    
    /**
     * 
     * @param EventInterface $e
     */
    public function onBootstrap(Event $e)
    {
        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();        
        $eventManager   = $application->getEventManager();
        $request        = $serviceManager->get('Request');

        if (!$request instanceof Request) {
            return;
        }
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($e) use ($serviceManager) {
            $serviceManager->get('LayoutManager')
                ->addHandle($e->getError());
        }, 100);
        
        $eventManager->attach($serviceManager->get('ConLayout\Listener\LayoutModifierListener'));
        $eventManager->attach($serviceManager->get('ConLayout\Listener\ActionHandlesListener'));
        $eventManager->attach($serviceManager->get('ConLayout\Listener\BodyClassListener'));
    }

    /**
     * 
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
