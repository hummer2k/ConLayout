<?php

use ConLayout\Controller\Plugin\LayoutManagerFactory;
use ConLayout\Zdt\Collector\LayoutCollector;
return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'zend-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/zend-developer-tools/toolbar/con-layout.phtml',
        ],
        // important: set empty layout template, so we
        // are able to set the template via layout()-helper in controller
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
];
