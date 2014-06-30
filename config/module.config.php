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
        // values: controller_action | routematch | combined 
        'handle_behavior' => 'combined',
        'config_glob_path' => './{module/*/config,design/module/*}/layout.config.php',
        'enable_debug' => false,
        'enable_cache' => false,
        'cache_dir' => './data/cache/con-layout'
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
