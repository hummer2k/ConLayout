<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'zend-developer-tools/toolbar/con-layout' => __DIR__ . '/../view/zend-developer-tools/toolbar/con-layout.phtml',
        ),
        'strategies' => array(
            'BlockRendererStrategy'
        ),
        // important: set empty layout template, so that we 
        // are able to set the template via layout()-helper in controller
        'layout' => ''
    ),
    'view_helpers' => array(
        'invokables' => array(
            'ConLayout\View\Helper\BodyClass' => 'ConLayout\View\Helper\BodyClass',            
        ),
        'aliases' => array(
            'bodyClass' => 'ConLayout\View\Helper\BodyClass',
        )
    ),
    'controller_plugins' => array(
        'factories' => array(
            'blockManager' => 'ConLayout\Controller\Plugin\BlockManagerFactory'
        ),
    ),
    'con-layout' => array(
        'handle_behavior' => \ConLayout\Listener\ActionHandlesListener::BEHAVIOR_COMBINED,
        'config_glob_paths' => array(
            'default' => './{vendor/*/*/config,module/*/config,design/module/*}/layout.config.php'
        ),
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
        'sorter' => array(
            'priorities' => array(
                'default'   => -20,
                 '\\'        => 'ConLayout\Priority\Segments::getPriority',
                '/'         => 'ConLayout\Priority\Segments::getPriority',
                '::'        => 'ConLayout\Priority\ControllerAction::getPriority',
                'error-'    => 15
            )
        ),
        'helpers' => array(
            'headLink' => array(
                'defaultMethod' => 'appendStylesheet',
            ),
            'headScript' => array(
                'defaultMethod' => 'appendFile',
            ),
            'inlineScript' => array(
                'defaultMethod' => 'appendFile',
            ),
            'headTitle' => array(
                'defaultMethod' => 'append'
            ),
            'headMeta' => array(
                'defaultMethod' => 'appendName'
            ),
            'bodyClass' => array(
                'defaultMethod' => 'append'
            ),
            'doctype' => array()
        ),
        'value_preparers' => array(
            'headLink' => array(
                'basePath' => 'ConLayout\ValuePreparer\BasePath'
            ),
            'headScript' => array(
                'basePath' => 'ConLayout\ValuePreparer\BasePath'
            ),
            'inlineScript' => array(
                'basePath' => 'ConLayout\ValuePreparer\BasePath'
            )
        )
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public/assets'
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
