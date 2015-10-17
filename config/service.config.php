<?php
return [
    'factories' => [
        'ConLayout\BlockManager' => 'ConLayout\BlockManagerFactory',

        'ConLayout\Block\Factory\BlockFactoryInterface' => 'ConLayout\Block\Factory\BlockFactoryFactory',

        'ConLayout\Listener\ActionHandlesListener' => 'ConLayout\Listener\Factory\ActionHandlesListenerFactory',
        'ConLayout\Listener\BodyClassListener' => 'ConLayout\Listener\Factory\BodyClassListenerFactory',
        'ConLayout\Listener\LoadLayoutListener' => 'ConLayout\Listener\Factory\LoadLayoutListenerFactory',
        'ConLayout\Listener\LayoutTemplateListener' => 'ConLayout\Listener\Factory\LayoutTemplateListenerFactory',
        'ConLayout\Listener\ViewHelperListener' => 'ConLayout\Listener\Factory\ViewHelperListenerFactory',
        'ConLayout\Listener\LayoutUpdateListener' => 'ConLayout\Listener\Factory\LayoutUpdateListenerFactory',

        'ConLayout\Layout\LayoutInterface' => 'ConLayout\Layout\LayoutFactory',
        'ConLayout\Updater\LayoutUpdaterInterface' => 'ConLayout\Updater\LayoutUpdaterFactory',
        'ConLayout\View\Renderer\BlockRenderer' => 'ConLayout\View\Renderer\BlockRendererFactory',
        'ConLayout\View\Strategy\BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategyFactory',

        'ConLayout\Zdt\Collector\LayoutCollector' => 'ConLayout\Zdt\Collector\LayoutCollectorFactory',

        'ConLayout\Options\ModuleOptions' => 'ConLayout\Options\ModuleOptionsFactory',
    ],
    'aliases' => [
        'Layout' => 'ConLayout\Layout\LayoutInterface',
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategy',
        'BlockRenderer' => 'ConLayout\View\Renderer\BlockRenderer',
        'BlockManager' => 'ConLayout\BlockManager'
    ],
    'invokables' => [
        'ConLayout\Listener\PrepareActionViewModelListener'
            => 'ConLayout\Listener\PrepareActionViewModelListener'
    ]
];
