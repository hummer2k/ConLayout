<?php
namespace ConLayout;

use ConLayout\ModuleManager\Feature\BlockProviderInterface;
use ConLayout\Options\ModuleOptions;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventInterface as Event;
use Zend\Http\PhpEnvironment\Request;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\FilterProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface,
    BootstrapListenerInterface,
    AutoloaderProviderInterface,
    InitProviderInterface,
    FilterProviderInterface
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
     * retrieve view helpers
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return include __DIR__ . '/../../config/viewhelper.config.php';
    }

    /**
     * retrieve filters
     *
     * @return array
     */
    public function getFilterConfig()
    {
        return include __DIR__ . '/../../config/filter.config.php';
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
            BlockProviderInterface::class,
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

        /* @var $options ModuleOptions */
        $options = $serviceManager->get(ModuleOptions::class);
        $listeners = $options->getListeners();

        foreach ($listeners as $listener => $isEnabled) {
            if ($isEnabled) {
                 $eventManager->attach($serviceManager->get($listener));
            }
        }
    }

    /**
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__
                ]
            ]
        ];
    }
}
