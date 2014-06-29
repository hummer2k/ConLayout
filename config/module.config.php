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
            'blocks' => 'ConLayout\Controller\Plugin\BlocksFactory'
        ),
    ),
    'con-layout' => array(
        // @TODO: implement combined handle behavior
        // values: controller_action | routematch | combined (not implemented yet)
        'handle_behavior' => 'controller_action',
        /*'layout' => array(
            'default' => array(
                'blocks' => array(
                    /*
                    'footer' => array(
                        'footer.block' => array(
                            'class' => 'Zend\View\Model\ViewModel',
                            'template' => 'layout/footer'
                        )
                    )
                )
            )
        )*/
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
