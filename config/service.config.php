<?php
return array(
    'factories' => array(
        'ConLayout\Service\LayoutService' => 'ConLayout\Service\LayoutServiceFactory',
        'ConLayout\Service\Config\CollectorInterface' => 'ConLayout\Service\Config\CollectorFactory',
        'ConLayout\Service\BlocksBuilder' => 'ConLayout\Service\BlocksBuilderFactory',
        'ConLayout\Service\LayoutModifier' => 'ConLayout\Service\LayoutModifierFactory',
        'ConLayout\Service\Config\SorterInterface' => 'ConLayout\Service\Config\SorterFactory',
        'ConLayout\Cache' => 'ConLayout\CacheFactory',
        'ConLayout\Listener\ActionHandlesListener' => 'ConLayout\Listener\ActionHandlesListenerFactory',
        'ConLayout\Listener\ContentViewModelsListener' => 'ConLayout\Listener\ContentViewModelsListenerFactory',
        'ConLayout\Listener\LayoutModifierListener' => 'ConLayout\Listener\LayoutModifierListenerFactory',
        'ConLayout\View\Renderer\BlockRenderer' => 'ConLayout\View\Renderer\BlockRendererFactory',
        'BlockRendererStrategy' => 'ConLayout\View\Strategy\BlockRendererStrategyFactory',
        'ConLayout\ValuePreparer\BasePath' => 'ConLayout\ValuePreparer\BasePathFactory',
        'ConLayout\Listener\BodyClassListener' => 'ConLayout\Listener\BodyClassListenerFactory',
    ),
    'invokables' => array(
        'ConLayout\Collector\LayoutCollector' => 'ConLayout\Collector\LayoutCollector'
    )
);
