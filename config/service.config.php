<?php
return [
    'factories' => [
        'ConLayout\Service\LayoutService' => 'ConLayout\Service\LayoutServiceFactory',
        'ConLayout\Config\CollectorInterface' => 'ConLayout\Config\CollectorFactory',
        'ConLayout\Service\BlocksBuilder' => 'ConLayout\Service\BlocksBuilderFactory',
        'ConLayout\Service\LayoutModifier' => 'ConLayout\Service\LayoutModifierFactory',
        'ConLayout\Config\SorterInterface' => 'ConLayout\Config\SorterFactory',
        'ConLayout\Cache' => 'ConLayout\CacheFactory',
        'ConLayout\Listener\ActionHandlesListener' => 'ConLayout\Listener\ActionHandlesListenerFactory',
        'ConLayout\Listener\LayoutModifierListener' => 'ConLayout\Listener\LayoutModifierListenerFactory',
        'ConLayout\View\Renderer\BlockRenderer' => 'ConLayout\View\Renderer\BlockRendererFactory',
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategyFactory',
        'ConLayout\AssetPreparer\BasePath' => 'ConLayout\AssetPreparer\BasePathFactory',
        'ConLayout\Listener\BodyClassListener' => 'ConLayout\Listener\BodyClassListenerFactory',
        'ConLayout\Debugger' => 'ConLayout\DebuggerFactory',
        'ConLayout\AssetPreparer\CacheBuster' => 'ConLayout\AssetPreparer\CacheBusterFactory',
    ],
    'invokables' => [
        'ConLayout\Zdt\Collector\LayoutCollector' => 'ConLayout\Zdt\Collector\LayoutCollector',
        'ConLayout\Config\Mutator\RemoveBlocks' => 'ConLayout\Config\Mutator\RemoveBlocks'
    ]
];
