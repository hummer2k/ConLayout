<?php
return [
    'factories' => [
        'ConLayout\Layout\LayoutInterface' => 'ConLayout\LayoutManagerFactory',
        'ConLayout\Listener\ActionHandlesListener' => 'ConLayout\Listener\Factory\ActionHandlesListenerFactory',
        'ConLayout\Listener\BodyClassListener' => 'ConLayout\Listener\Factory\BodyClassListenerFactory',
        'ConLayout\View\Renderer\BlockRenderer' => 'ConLayout\View\Renderer\BlockRendererFactory',
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategyFactory',
        'ConLayout\AssetPreparer\BasePath' => 'ConLayout\AssetPreparer\BasePathFactory',
        'ConLayout\AssetPreparer\CacheBuster' => 'ConLayout\AssetPreparer\CacheBusterFactory',
        'ConLayout\Debug\Debugger' => 'ConLayout\Debug\DebuggerFactory',
    ],
    'aliases' => [
        'Layout' => 'ConLayout\Layout\LayoutInterface',
    ],
    'invokables' => [
        'ConLayout\Zdt\Collector\LayoutCollector' => 'ConLayout\Zdt\Collector\LayoutCollector',
    ]
];
