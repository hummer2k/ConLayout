<?php

return array(
    'default' => array(
        'layout' => 'layout/2cols-left',
        'handles' => [
            'my-custom-handle'
        ],
        'blocks' => array(
            'header' => array(
                'block.header' => array(
                    'class' => 'ConLayout\Block\Dummy'
                )
            )
        )
    ),
    'route' => array(
        'blocks' => array(
            'sidebar.right' => array(
                'block.sidebar.right' => array(
                    'class' => 'ConLayout\Block\Dummy',
                    'children' => array(
                        'childCapture' => array(
                            'block.sidebar.right.child1' => array(
                                'class' => 'ConLayout\Block\Dummy'
                            )
                        )
                    )
                )
            )
        )
    ),
    'controller::action' => array(
        'layout' => 'layout/1col'
    ),
    'route/childroute' => array(
        'layout' => 'layout/2cols-right',
        'blocks' => array(
            'sidebar' => array(
                'block.sidebar' => array(
                    'class' => 'ConLayout\Block\Dummy'
                ),
                'block.sidebar.before' => array(
                    'template' => 'lorem/ipsum',
                    'options' => array(
                        'order' => -10
                    )
                )
            ),
        )
    ),
    'remove-handle' => array(
        'blocks' => array(
            '_remove' => array(
                'block.header' => true
            )
        )
    )
);
