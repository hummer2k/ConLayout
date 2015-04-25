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
        'aliases' => [
            'bodyClass' => 'ConLayout\View\Helper\BodyClass',
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            'layoutManager' => 'ConLayout\Controller\Plugin\LayoutManagerFactory'
        ]
    ],
    'con-layout' => [
        'update_listener_glob_paths' => [
            'default' => __DIR__ . '/layout.config.php',
            'xml' => __DIR__ . '/layout.config.xml'
        ],
        'enable_debug' => false,
        'child_capture_to' => 'childHtml',
        'cache_buster_internal_base_dir' => './pub',
        'view_helpers' => [
            'doctype' => [],
            'headLink' => [
                'default_method' => 'appendStylesheet',
            ],
            'headScript' => [
                'default_method' => 'appendFile',
            ],
            'inlineScript' => [
                'default_method' => 'appendFile',
            ],
            'headTitle' => [
                'default_method' => 'append'
            ],
            'headMeta' => [
                'default_method' => 'appendName'
            ],
            'bodyClass' => [
                'default_method' => 'addClass'
            ],            
        ],
        'asset_preparers' => [
            'headLink' => [
                'basePath' => 'ConLayout\AssetPreparer\BasePath',
                'cacheBuster' => 'ConLayout\AssetPreparer\CacheBuster',
            ],
            'headScript' => [
                'basePath' => 'ConLayout\AssetPreparer\BasePath',
                'cacheBuster' => 'ConLayout\AssetPreparer\CacheBuster',
            ],
            'inlineScript' => [
                'basePath' => 'ConLayout\AssetPreparer\BasePath',
                'cacheBuster' => 'ConLayout\AssetPreparer\CacheBuster',
            ]
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
