<?php
return array(
    'factories' => array(
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
        'ConLayout\ValuePreparer\BasePath' => 'ConLayout\ValuePreparer\BasePathFactory',
        'ConLayout\Listener\BodyClassListener' => 'ConLayout\Listener\BodyClassListenerFactory',
        'ConLayout\Debugger' => 'ConLayout\DebuggerFactory',
        'ConLayout\ValuePreparer\CacheBuster' => 'ConLayout\ValuePreparer\CacheBusterFactory',
    ),
    'invokables' => array(
        'ConLayout\Collector\LayoutCollector' => 'ConLayout\Collector\LayoutCollector',
        'ConLayout\Config\Modifier\RemoveBlocks' => 'ConLayout\Config\Modifier\RemoveBlocks'
    )
);
