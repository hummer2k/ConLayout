# ConLayout - Easy handling ZF2-layouts

## Installation

### 1. Clone repo:

    $ git submodule add git@bitbucket.org:conlabz/conlayout.git corporate/ConLayout

### 2. Enable Module in your application.config.php

    <?php
    $config = [
        'modules' => [
            'ConLayout', // <--
            'Application',
            'SomeModule'
        ]
    ];

## Usage

Layouts are controlled by handles.

Handles may be: (ordered by priority ASC)

* global (= default)
* a controller namespace (e.g. Application, Application\Controller, Application\Controller\Index) (the more specific, the higher the priority)
* a route (e.g. user/login) (the more child routes, the higher the priority)
* an action (e.g. Application\Controller\Index::index)

(The current handles are listed in zend developer toolbar)

## Layout config structure

    <?php
    return [
        // e.g. default = global
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
            // apply config for other handles
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

## ACL

An Event is fired before the block instance is injected into the layout where you can determine wheter the block is allowed to be shown. (return true or false in your event listener)

    <?php
    // Module.php
    /**
     * @param \Zend\EventManager\EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $application    = $e->getApplication();
        $eventManager   = $application->getEventManager();

        // get the authorization service 
        $authService = $application->getServiceLocator('My\AuthService');
        
        $eventManager->getSharedManager()
            ->attach('ConLayout\Service\LayoutModifier', 'isAllowed', function($e) use ($authService) {
            $block = $e->getParam('block');
            $resource = $block['name'];
            return $authService->isGranted($resource);
        });
    }

## Blocks/ViewModels

Blocks aka ViewModels are classes with a template to provide view specific data. 
For example, to display a widget in a sidebar, a page fragment like header, footer 
or navigation. 

Let's create a widget to display the current time:


    <?php
    namespace ConLayout\Block;
    
    class CurrentTime extends AbstractBlock
    {
        protected $template = 'path/to/current-time';
        
        public function getCurrentTime()
        {
            return (new \DateTime())->format('d.m.Y H:i:s');
        }
    }


    <?php
    // path/to/current-time.phtml 
    <article class="current-time widget">
        <?php 
            // non-existing variables will be mapped to viewmodel's 'get' . $VarName() method.
            // In this case CurrentTime::getCurrentTime()
        ?>
        <?= $this->currentTime ?>
    </article>


tell the layout to place our widget in the sidebar


    <?php
    // layout.config.php
    return [
        'default' => [
            'blocks' => [
                'sidebarLeft' => [
                    'current.time.widget' => [
                        'class' => 'ConLayout\Block\CurrentTime'
                    ]
                ]
            ]
        ]        
    ];


## Caching

Implement ``ConLayout\Block\CacheableInterface`` or extend from ``ConLayout\Block\AbstractBlock`` and define a unique cache key and ttl.
