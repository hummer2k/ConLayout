<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'zend-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/zend-developer-tools/toolbar/con-layout.phtml',
        ],
        'strategies' => [
            'BlockRendererStrategy'
        ],
        // important: set empty layout template, so we
        // are able to set the template via layout()-helper in controller
        'layout' => ''
    ],
    'view_helpers' => [
        'invokables' => [
            'ConLayout\View\Helper\BodyClass' => 'ConLayout\View\Helper\BodyClass',
        ],
        'factories' => [
            'ConLayout\View\Helper\Block' => 'ConLayout\View\Helper\BlockFactory'
        ],
        'aliases' => [
            'bodyClass' => 'ConLayout\View\Helper\BodyClass',
            'block' => 'ConLayout\View\Helper\Block'
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            'layoutManager' => 'ConLayout\Controller\Plugin\LayoutManagerFactory'
        ]
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public/assets'
            ]
        ]
    ],
    'zenddevelopertools' => [
        'profiler' => [
            'collectors' => [
                'con-layout' => 'ConLayout\Zdt\Collector\LayoutCollector',
            ],
        ],
        'toolbar' => [
            'entries' => [
                'con-layout' => 'zend-developer-tools/toolbar/con-layout',
            ],
        ],
    ],
];
