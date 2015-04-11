<?php
return [
    'factories' => [
        'ConLayout\LayoutManagerInterface' => 'ConLayout\LayoutManagerFactory',
        'ConLayout\Service\LayoutService' => 'ConLayout\Service\LayoutServiceFactory',
        'ConLayout\Config\CollectorInterface' => 'ConLayout\Config\CollectorFactory',
        'ConLayout\Listener\ActionHandlesListener' => 'ConLayout\Listener\ActionHandlesListenerFactory',
        'ConLayout\Listener\LayoutModifierListener' => 'ConLayout\Listener\LayoutModifierListenerFactory',
        'ConLayout\View\Renderer\BlockRenderer' => 'ConLayout\View\Renderer\BlockRendererFactory',
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategyFactory',
        'ConLayout\AssetPreparer\BasePath' => 'ConLayout\AssetPreparer\BasePathFactory',
        'ConLayout\Listener\BodyClassListener' => 'ConLayout\Listener\BodyClassListenerFactory',
        'ConLayout\Debug\Debugger' => 'ConLayout\Debug\DebuggerFactory',
        'ConLayout\AssetPreparer\CacheBuster' => 'ConLayout\AssetPreparer\CacheBusterFactory',
    ],
    'aliases' => [
        'LayoutManager' => 'ConLayout\LayoutManagerInterface',
    ],
    'invokables' => [
        'ConLayout\Zdt\Collector\LayoutCollector' => 'ConLayout\Zdt\Collector\LayoutCollector',
    ]
];
