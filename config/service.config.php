<?php
return array(
    'factories' => array(
        'ConLayout\Service\LayoutService' => 'ConLayout\Service\LayoutServiceFactory',
        'ConLayout\Service\Config\CollectorInterface' => 'ConLayout\Service\Config\CollectorFactory',
        'ConLayout\Service\BlocksBuilder' => 'ConLayout\Service\BlocksBuilderFactory',
        'ConLayout\Service\LayoutModifier' => 'ConLayout\Service\LayoutModifierFactory',
        'ConLayout\Service\Config\SorterInterface' => 'ConLayout\Service\Config\SorterFactory',
        'ConLayout\Cache' => 'ConLayout\CacheFactory',
        'ConLayout\Listener\ActionHandles' => 'ConLayout\Listener\ActionHandlesFactory',
        'ConLayout\View\Renderer\BlockRenderer' => 'ConLayout\View\Renderer\BlockRendererFactory',
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategyFactory'
    ),
    'invokables' => array(
        'ConLayout\Collector\LayoutCollector' => 'ConLayout\Collector\LayoutCollector'
    )
);
