<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            './design/coad/twbs3/template',
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'zend-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/zend-developer-tools/toolbar/con-layout.phtml',
        )
    ),
    'controller_plugins' => array(
        'factories' => array(
            'blockManager' => 'ConLayout\Controller\Plugin\BlockManagerFactory'
        ),
    ),
    'con-layout' => array(
        'handle_behavior' => 'combined', // controller_action | routematch | combined 
        'config_glob_path' => './{module/*/config,design/module/*}/layout.config.php',
        'enable_debug' => false,
        'enable_cache' => false,
        'cache_dir' => './data/cache/con-layout',
        'child_capture_to' => 'childHtml'
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
