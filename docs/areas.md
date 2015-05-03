# Areas

With areas you can tell the layout update listener which layout updates to fetch.
E.g. frontend, backend etc.

Before you can use this feature, you have to tell the layout update listener
in which area we are currently in.


````php
<?php
$area = 'frontend'; // or 'backend' or 'admin' ...

/* @var $layoutUpdateListener \ConLayout\Listener\LayoutUpdateListener */
$layoutUpdateListener = $sm->get('ConLayout\Listener\LayoutUpdateListener');
$layoutUpdateListener->setArea($area);

````

Now you could add the paths for the areas in some module configuration:

````php
<?php
// module.config.php in Application module
return [
    // ...
    'con-layout' => [
        'layout_update_paths' => [
            'frontend' => [
                __DIR__ . '/../layout/frontend'
            ],
            'backend' => [
                __DIR__ . '/../layout/backend'
            ]
        ]
    ]
    // ...
];
````

### How to determine the current area?

A possible implementation could be to listen to the MVC dispatch event
and determine the area by the matched route name:


````php
<?php

namespace Application\Listener;

use ConLayout\Listener\LayoutUpdateListener;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;

class AreaListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    const AREA_FRONTEND = 'frontend';
    const AREA_BACKEND  = 'backend';
    const AREA_DEFAULT  = self::AREA_FRONTEND;

    /**
     * @var LayoutUpdateListener
     */
    protected $layoutUpdateListener;

    public function __construct(LayoutUpdateListener $layoutUpdateListener)
    {
        $this->layoutUpdateListener = $layoutUpdateListener;
    }

    public function attach(EventManagerInterface $events)
    {
        $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch']);
    }

    public function onDispatch(MvcEvent $e)
    {
        $area = self::AREA_DEFAULT;

        $routeMatch = $e->getRouteMatch();
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        // example for zfc admin
        if (fnmatch('zfcadmin*', $matchedRouteName)) {
            $area = self::AREA_BACKEND;
        }

        $this->layoutUpdateListener->setArea($area);
    }
}
````
