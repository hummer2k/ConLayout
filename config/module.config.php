<?php

use ConLayout\Controller\Plugin\LayoutManagerFactory;
use ConLayout\Ldt\Collector\LayoutCollector;
use ConLayout\Block\Container;

return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'laminas-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/laminas-developer-tools/toolbar/con-layout.phtml',
        ],
        'layout' => ''
    ],
    'controller_plugins' => [
        'factories' => [
            'layoutManager' => LayoutManagerFactory::class
        ]
    ],
    'laminas-developer-tools' => [
        'profiler' => [
            'collectors' => [
                'con-layout' => LayoutCollector::class,
            ],
        ],
        'toolbar' => [
            'entries' => [
                'con-layout' => 'laminas-developer-tools/toolbar/con-layout',
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
