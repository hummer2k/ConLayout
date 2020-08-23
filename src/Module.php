<?php

namespace ConLayout;

use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\DebugFilter;
use ConLayout\Layout\LayoutInterface;
use ConLayout\ModuleManager\Feature\BlockProviderInterface;
use ConLayout\Options\ModuleOptions;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventInterface as Event;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\FilterProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;
use Laminas\ModuleManager\Feature\ViewHelperProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Filter\FilterChain;
use Psr\Container\ContainerInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface,
    BootstrapListenerInterface,
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
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/con-layout.global.php.dist'
        );
    }

    /**
     * retrieve view helpers
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return include __DIR__ . '/../config/viewhelper.config.php';
    }

    /**
     * retrieve filters
     *
     * @return array
     */
    public function getFilterConfig()
    {
        return include __DIR__ . '/../config/filter.config.php';
    }

    /**
     * retrieve services
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../config/service.config.php';
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
     * @param MvcEvent|EventInterface $e
     * @return array|void
     */
    public function onBootstrap(Event $e)
    {
        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager   = $application->getEventManager();
        $request        = $serviceManager->get('Request');

        if (!$request instanceof HttpRequest) {
            return;
        }

        /* @var $options ModuleOptions */
        $options = $serviceManager->get(ModuleOptions::class);
        $listeners = $options->getListeners();

        foreach ($listeners as $listener => $isEnabled) {
            if ($isEnabled) {
                 $serviceManager->get($listener)->attach($eventManager);
            }
        }

        $serviceManager->get(LayoutInterface::class)->setRoot($e->getViewModel());

        if ($serviceManager->has('FilterManager')) {
            $this->attachContainerFilter($serviceManager);
            if ($options->isDebug()) {
                $this->attachDebugger($serviceManager);
            }
        }
    }

    /**
     * @param ContainerInterface $container
     */
    private function attachContainerFilter(ContainerInterface $container)
    {
        /** @var PhpRenderer $renderer */
        $renderer = $container->get(PhpRenderer::class);
        $filterManager = $container->get('FilterManager');
        $renderer->getFilterChain()->attach($filterManager->get(ContainerFilter::class), 20);
    }

    /**
     * @param ContainerInterface $serviceManager
     */
    private function attachDebugger(ContainerInterface $serviceManager)
    {
        /** @var PhpRenderer $renderer */
        $renderer = $serviceManager->get(PhpRenderer::class);
        $filterManager = $serviceManager->get('FilterManager');
        $renderer->getFilterChain()->attach($filterManager->get(DebugFilter::class), 10);
    }
}
