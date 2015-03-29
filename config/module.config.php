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
            'blockManager' => 'ConLayout\Controller\Plugin\BlockManagerFactory'
        ],
    ],
    'con-layout' => [
        'handle_behavior' => \ConLayout\Listener\ActionHandlesListener::BEHAVIOR_COMBINED,
        'config_glob_paths' => [
            'default' => './{vendor/*/*/config,module/*/config,design/module/*}/layout.config.php'
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
            'internal_base_dir' => './public'
        ],
        'block_config_modifiers' => [
            'ConLayout\Config\Modifier\RemoveBlocks'
        ],
        'sorter' => [
            'priorities' => [
                'default'   => -1,
                 '\\'       => 'ConLayout\Priority\Segments::getPriority',
                '/'         => 'ConLayout\Priority\Segments::getPriority',
                '::'        => 'ConLayout\Priority\ControllerAction::getPriority',
                'error-'    => 15
            ]
        ],
        'helpers' => [
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
        'value_preparers' => [
            'headLink' => [
                'basePath' => 'ConLayout\ValuePreparer\BasePath'
            ],
            'headScript' => [
                'basePath' => 'ConLayout\ValuePreparer\BasePath'
            ],
            'inlineScript' => [
                'basePath' => 'ConLayout\ValuePreparer\BasePath'
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
                'con-layout' => 'ConLayout\Collector\LayoutCollector',
            ],
        ],
        'toolbar' => [
            'entries' => [
                'con-layout' => 'zend-developer-tools/toolbar/con-layout',
            ],
        ],
    ],
];
