ConLayout - Easy handling ZF2-layouts
=====================================

Installation
------------

    $ git submodule add git@bitbucket.org:conlabz/conlayout.git corporate/ConLayout

Usage
-----

Layouts are controlled by handles.

Handles may be: (ordered by priority ASC)

* global (= default)
* a module (e.g Application)
* a controller (e.g. Application\Controller\Index)
* a route (e.g. user/login) (the more child routes, the higher the priority)
* an action (e.g. Application\Controller\Index::index)

(The current handles are listed in zend developer toolbar)

Layout config structure
-----------------------

    <?php
    return [
        // e.g. default
        'HANDLE' => [
            // set layout template for HANDLE
            'layout' => 'path/to/layout-template',
            // add helpers
            'headLink' => [
                // @see http://framework.zend.com/manual/2.3/en/modules/zend.view.helpers.head-link.html
                // just default stylesheet
                'css/styles.css',
                // more precise
                [
                    'method' => 'prependStylesheet',
                    'args' => ['//cdn.example.com/css/styles.css', 'screen']
                ],
                // with conditional
                ['//cdn.example.org/css/ie8.css', 'screen', 'lt IE 9']
            ],
            'headScript' => [
                // @see http://framework.zend.com/manual/2.3/en/modules/zend.view.helpers.head-script.html
                // @see headLink
            ],
            'inlineScript' => [
                // @see headScript
            ]
            'headTitle' => [
                // @see http://framework.zend.com/manual/2.3/en/modules/zend.view.helpers.head-title.html
                'My Title',
                'another title appended'
            ],
            'headMeta' => [
                ['viewport', 'width=device-width, initial-scale=1.0, user-scalable=no']          
            ]
            'doctype' => 'HTML5',
            // apply config for other handles?
            'handles' => [
                'user/login',
                'Application\Controller\Index'
            ],
            'blocks' => [
                // your captureTo var in layout template, e.g. sidebarLeft, header, footer...
                'CAPTURE_TO' => [
                    // BLOCK_NAME = unique block identifier, required
                    'BLOCK_NAME' => [
                        // Can extend from ConLayout\Block\AbstractBlock
                        // Should implement ConLayout\Block\BlockInterface
                        // optional, default: Zend\View\ViewModel                        
                        'class'     => 'ConLayout\Block\Dummy',
                        // only optional when declared in block class
                        'template'  => 'path/to/block-template',
                        // process some actions (execute block methods)
                        'actions'   => [
                            'method' => ['param1', 'param2', 'param3']
                        ],
                        // acl resource name
                        // optional defaults to BLOCK_NAME
                        'resource' => 'some_resource_id',
                        // sort order
                        'order' => 5,
                        'children' => [
                            // if CAPTURE_TO index is integer, defaults to 'childHtml' or configured con-layout/capture_to node
                            'CAPTURE_TO' => [
                                'BLOCK_NAME' => [
                                    'template' => 'path/to/child-block-template'
                                ]
                            ]
                        ]
                    ]
                ],
                '_remove' => [
                    // remove block
                    'BLOCK_NAME' => true,
                    // you may overwrite by another handle to not remove a specified block:
                    'BLOCK_NAME' => false
                ]
            ]
        ],
    ];
    
ACL
---

Example BjyAuthorize:

    // Module.php
    /**
     * 
     * @param \Zend\EventManager\EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        
        $auth = $serviceManager->get('BjyAuthorize\Service\Authorize');
        $acl  = $auth->getAcl();
        $role = $auth->getIdentity();
        
        \ConLayout\Service\LayoutModifier::setDefaultAcl($acl);
        \ConLayout\Service\LayoutModifier::setDefaultRole($role);
    }

Blocks
------

coming soon...

Caching
-------

coming soon...