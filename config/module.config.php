<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            './design/coad/twbs3/template',
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'zend-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/zend-developer-tools/toolbar/con-layout.phtml',
        ),
        'strategies' => array(
            'BlockRendererStrategy'
        )
    ),
    'controller_plugins' => array(
        'factories' => array(
            'blockManager' => 'ConLayout\Controller\Plugin\BlockManagerFactory'
        ),
    ),
    'con-layout' => array(
        'handle_behavior' => \ConLayout\Listener\ActionHandles::BEHAVIOR_COMBINED,
        'config_glob_path' => './{vendor/*/*/config,module/*/config,design/module/*}/layout.config.php',
        'enable_debug' => false,
        'enable_cache' => false,
        'cache_dir' => './data/cache/con-layout',
        'child_capture_to' => 'childHtml',
        'sorter' => array(
            'priorities' => array(
                'default'   => -20,
                '\\'        => 0,
                '/'         => function($handle, $substr) {
                    return substr_count($handle, $substr);
                },
                '::'        => 10
            )
        )
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'css/styles.css' => array(
                    'css/con-layout.css'
                )
            ),
            'paths' => array(
                __DIR__ . '/../public'
            )
        )
    ),
    'zenddevelopertools' => array(
        'profiler' => array(
            'collectors' => array(
                'con-layout' => 'ConLayout\Collector\LayoutCollector',
            ),
        ),
        'toolbar' => array(
            'entries' => array(
                'con-layout' => 'zend-developer-tools/toolbar/con-layout',
            ),
        ),
    ),
);
