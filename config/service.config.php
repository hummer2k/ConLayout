<?php
return array(
    'factories' => array(
        'ConLayout\Service\Config' => 'ConLayout\Service\ConfigFactory',
        'ConLayout\Service\Config\CollectorInterface' => 'ConLayout\Service\Config\CollectorFactory',
        'ConLayout\Service\BlocksBuilder' => 'ConLayout\Service\BlocksBuilderFactory',
        'ConLayout\Service\LayoutModifier' => 'ConLayout\Service\LayoutModifierFactory',
        'ConLayout\Cache' => 'ConLayout\CacheFactory'
    ),
    'invokables' => array(
        'ConLayout\Collector\LayoutCollector' => 'ConLayout\Collector\LayoutCollector'
    )
);
