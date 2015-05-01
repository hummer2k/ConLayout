<?php
return [
    'factories' => [
        'ConLayout\AssetPreparer\BasePath' => 'ConLayout\AssetPreparer\BasePathFactory',
        'ConLayout\AssetPreparer\CacheBuster' => 'ConLayout\AssetPreparer\CacheBusterFactory',

        'ConLayout\Block\Factory\BlockFactoryInterface' => 'ConLayout\Block\Factory\BlockFactoryFactory',
        'ConLayout\Debug\Debugger' => 'ConLayout\Debug\DebuggerFactory',

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
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategy'
    ],
    'invokables' => [
        'ConLayout\AssetPreparer\Delegator\AddAssetPreparerDelegator'
            => 'ConLayout\AssetPreparer\Delegator\AddAssetPreparerDelegator',
        'ConLayout\Listener\PrepareActionViewModelListener'
            => 'ConLayout\Listener\PrepareActionViewModelListener'
    ],
    'delegators' => [
        'ConLayout\Listener\ViewHelperListener' => [
            'ConLayout\AssetPreparer\Delegator\AddAssetPreparerDelegator'
        ]
    ]
];
