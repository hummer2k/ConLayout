<?php

use ConLayout\Controller\Plugin\LayoutManagerFactory;
use ConLayout\Zdt\Collector\LayoutCollector;
use ConLayout\Block\Container;

return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'zend-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/zend-developer-tools/toolbar/con-layout.phtml',
        ],
        'layout' => ''
    ],
    'controller_plugins' => [
        'factories' => [
            'layoutManager' => LayoutManagerFactory::class
        ]
    ],
    'zenddevelopertools' => [
        'profiler' => [
            'collectors' => [
                'con-layout' => LayoutCollector::class,
            ],
        ],
        'toolbar' => [
            'entries' => [
                'con-layout' => 'zend-developer-tools/toolbar/con-layout',
            ],
        ],
    ],
    'blocks' => [
        'invokables' => [
            'container' => Container::class
        ],
        'shared' => [
            'container' => false
        ]
    ]
];
