<?php
namespace ConLayout;

use ConLayout\Handle\Handle;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventInterface as Event;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;

/**
 * ConLayout\Module
 * 
 * 
 */
class Module implements ConfigProviderInterface
{
    /**
     * retrieve module config
     * 
     * @return array
     */
    public function getConfig()
    {
        return array_merge(
            include __DIR__ . '/../../config/module.config.php',
            include __DIR__ . '/../../config/con-layout.config.php.dist'
        );
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

        $listeners = [
            'ConLayout\Listener\ActionHandlesListener',
            'ConLayout\Listener\LayoutUpdateListener',
            'ConLayout\Listener\LoadLayoutListener',
            'ConLayout\Listener\LayoutTemplateListener',
            'ConLayout\Listener\ViewHelperListener'
        ];

        foreach ($listeners as $listener) {
            $eventManager->attach($serviceManager->get($listener));
        }
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
