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
        'update_listener' => [
            'glob_paths' => [
                'default' => __DIR__ . '/layout.config.php',
                'xml' => __DIR__ . '/layout.config.xml'
            ]
        ],
        'enable_debug' => false,
        'enable_layout_cache' => false,
        'enable_block_cache' => false,
        'layout_cache' => 'ConLayout\Cache',
        'block_cache' => 'ConLayout\Cache',
        'cache_dir' => './data/cache/con-layout',
        'child_capture_to' => 'childHtml',
        'content_capture_to' => 'content',
        'cache_buster' => [
            'internal_base_dir' => './pub'
        ],
        'view_helpers' => [
            'headLink' => [
                'defaultMethod' => 'appendStylesheet',
            ],
            'headScript' => [
                'defaultMethod' => 'appendFile',
            ],
            'inlineScript' => [
                'defaultMethod' => 'appendFile',
            ],
            'headTitle' => [
                'defaultMethod' => 'append'
            ],
            'headMeta' => [
                'defaultMethod' => 'appendName'
            ],
            'bodyClass' => [
                'defaultMethod' => 'append'
            ],
            'doctype' => []
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
