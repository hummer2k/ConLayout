<?php

declare(strict_types=1);

namespace ConLayout;

use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\DebugFilter;
use ConLayout\Layout\LayoutInterface;
use ConLayout\ModuleManager\Feature\BlockProviderInterface;
use ConLayout\Options\ModuleOptions;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventInterface as Event;
use Laminas\Filter\FilterInterface;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\FilterProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;
use Laminas\ModuleManager\Feature\ViewHelperProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

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
     */
    public function getConfig(): array
    {
        return array_merge(
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/con-layout.global.php.dist'
        );
    }

    /**
     * retrieve view helpers
     */
    public function getViewHelperConfig(): array
    {
        return include __DIR__ . '/../config/viewhelper.config.php';
    }

    /**
     * retrieve filters
     */
    public function getFilterConfig(): array
    {
        return include __DIR__ . '/../config/filter.config.php';
    }

    /**
     * retrieve services
     */
    public function getServiceConfig(): array
    {
        return include __DIR__ . '/../config/service.config.php';
    }

    public function init(ModuleManagerInterface $manager): void
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

    public function onBootstrap(Event $e): void
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
            /** @var ContainerInterface $filterManager */
            $filterManager = $serviceManager->get('FilterManager');
            /** @var PhpRenderer $phpRenderer */
            $phpRenderer = $serviceManager->get(PhpRenderer::class);
            $this->attachFilter($phpRenderer, $filterManager->get(ContainerFilter::class), 20);
            if ($options->isDebug()) {
                $this->attachFilter($phpRenderer, $filterManager->get(DebugFilter::class), 10);
            }
        }
    }

    private function attachFilter(PhpRenderer $phpRenderer, FilterInterface $filter, int $priority): void
    {
        $phpRenderer->getFilterChain()->attach($filter, $priority);
    }
}
