<?php
namespace ConLayout;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventInterface as Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface,
    BootstrapListenerInterface,
    AutoloaderProviderInterface,
    InitProviderInterface
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
            include __DIR__ . '/../../config/con-layout.global.php.dist'
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
     * @param ModuleManagerInterface $manager
     */
    public function init(ModuleManagerInterface $manager)
    {
        $sm = $manager->getEvent()->getParam('ServiceManager');
        $serviceListener = $sm->get('ServiceListener');
        $serviceListener->addServiceManager(
            'BlockManager',
            'blocks',
            'ConLayout\ModuleManager\Feature\BlockProviderInterface',
            'getBlockConfig'
        );
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
            'ConLayout\Listener\ViewHelperListener',
            'ConLayout\Listener\PrepareActionViewModelListener'
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
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__
                ]
            ]
        ];
    }
}
