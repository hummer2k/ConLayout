# ConLayout Tutorial with ZF2 skeleton application

In this tutorial I want to show you the basic functionality of ConLayout.
It is assumed that you already have set up a 
[zf2 skeleton application](https://github.com/zendframework/ZendSkeletonApplication) with 
[ZendDeveloperTools](https://github.com/zendframework/ZendDeveloperTools) and
installed the ConLayout module.
We will cover basic configuration on module-basis and you will get to know the 
layout instructions `layout`, `blocks`, `remove_blocks` and 
`include`.

## Table of contents

1. Configuration
2. Handles
3. Handle priorities
4. Getting started, define a different layout template for a specific page
5. Use blocks to reuse parts of the layout
6. Place a widget block in the sidebar
7. Create a custom block and add some logic to it
8. Include a handle
9. Remove blocks from a specific page

## 1. Configuration

Before we start, we have to do a little configuration. 

Create the directory `layout` inside the Application module directory. In this 
directory we will put our layout update instructions.

Open `module/Application/config/module.config.php` and add a new layout update
path that points to this directory. 

````php
// module/Application/config/module.config.php
use ConLayout\Updater\LayoutUpdaterInterface;
return [
    // ...
    'con-layout' => [
        'layout_update_paths' => [
            LayoutUpdaterInterface::AREA_GLOBAL => [
                __DIR__ . '/../layout'
            ]
        ]
    ],
    // ...
];
````

Note: In this tutorial we use the `global` area (`LayoutUpdaterInterface::AREA_GLOBAL`) 
since we do not have any other areas like a backend. (More information about 
areas can be found in /docs/areas.md)

## 2. Handles

With handles we tell the `LayoutUpdater` which instructions it has to fetch.

Open your browser and navigate to your zf2 skeleton installation. 

The ZendDeveloperToolbar shows you all handles for the current request:

![handles](https://dl.dropboxusercontent.com/u/4741060/conlayout/tutorial/handles.png)

### `default`

The `default` handle is present on every request. 
With this handle we define common layout instructions that are available most of 
the time.

### `application`

The `application` handle is present on every page with the controller namespace 
Application

### `application/index`

`application/index` applies to every action of the controller
`Application\Controller\IndexController`

### `application/index/index`

This handle is only valid for the action 
`Application\Controller\IndexController::indexAction`

## 3. Handle priorities

Every handle has a priority. 

Layout instructions for a handle with a higher priority will simply override
instructions for a handle with a lower priority. For example 
`application` will override `default`, `application/index` will override
`application` and so on.

## 4. Getting started, define a different layout template for a specific page

First, add a new action, let's say `view`, in 
`Application\Controller\IndexController.php`

````php
<?php
// ...
class IndexController extends AbstractActionController
{
    // ...

    public function viewAction()
    {
        return new ViewModel();
    }
    
    // ...
}
````

Don't forget to create a template for this action: `Application/view/application/index/view.phtml`

Navigate to http://your.zf2-skeleton.app/application/index/view and open the 'Layout' 
entry in the zend developer toolbar. 

In the section 'Handles' the handles of the current request are listed.

![zdt-handles](https://dl.dropboxusercontent.com/u/4741060/conlayout/tutorial/handles-app-view.png)

We now take the `application/index/view` handle to set a different layout 
template for this page or action respectively.

Create a file named after the handle `application/index/view` in your previously
configured layout update path `module/Application/layout` 

````php
<?php
// module/Application/layout/application/index/view.php
return [
    'layout' => 'layout/2cols-left'
];
````

Now create a new layout template with 2 columns. For simplicity we just copy the 
`Application/view/layout/layout.phtml` to `Application/view/layout/2cols-left.phtml` 
and add a sidebar:

````php
<?php // module/Application/view/layout/2cols-left.phtml ?>
<?php echo $this->doctype(); ?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <?php echo $this->headTitle('ZF2 '. $this->translate('Skeleton Application'))->setSeparator(' - ')->setAutoEscape(false) ?>

        <?php echo $this->headMeta()
            ->appendName('viewport', 'width=device-width, initial-scale=1.0')
            ->appendHttpEquiv('X-UA-Compatible', 'IE=edge')
        ?>

        <!-- Le styles -->
        <?php echo $this->headLink(array('rel' => 'shortcut icon', 'type' => 'image/vnd.microsoft.icon', 'href' => $this->basePath() . '/img/favicon.ico'))
                        ->prependStylesheet($this->basePath('css/style.css'))
                        ->prependStylesheet($this->basePath('css/bootstrap-theme.min.css'))
                        ->prependStylesheet($this->basePath('css/bootstrap.min.css')) ?>

        <!-- Scripts -->
        <?php echo $this->headScript()
            ->prependFile($this->basePath('js/bootstrap.min.js'))
            ->prependFile($this->basePath('js/jquery.min.js'))
            ->prependFile($this->basePath('js/respond.min.js'), 'text/javascript', array('conditional' => 'lt IE 9',))
            ->prependFile($this->basePath('js/html5shiv.js'),   'text/javascript', array('conditional' => 'lt IE 9',))
        ; ?>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo $this->url('home') ?>"><img src="<?php echo $this->basePath('img/zf2-logo.png') ?>" alt="Zend Framework 2"/>&nbsp;<?php echo $this->translate('Skeleton Application') ?></a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="<?php echo $this->url('home') ?>"><?php echo $this->translate('Home') ?></a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <h3>Sidebar</h3>
                </div>
                <div class="col-sm-9">
                    <?php echo $this->content; ?>
                </div>
            </div>
            <hr>
            <footer>
                <p>&copy; 2005 - <?php echo date('Y') ?> by Zend Technologies Ltd. <?php echo $this->translate('All rights reserved.') ?></p>
            </footer>
        </div> <!-- /container -->
        <?php echo $this->inlineScript() ?>
    </body>
</html>
````

The result should be something like that:

![layout-2cols](https://dl.dropboxusercontent.com/u/4741060/conlayout/tutorial/2cols-left.png)

## 5. Use blocks to reuse parts of the layout

Because duplicated code is always a bad idea and hard to maintain, we want to
reuse some parts in different layouts. This is where blocks come into play:

Create a new directory `partials` under `Application/view/layout`.

In this directory we now create our partials `head.phtml`, `header.phtml` and 
`footer.phtml`

Move the head/header/footer markup into the according partial templates and 
replace them by a placeholder `<?= $this->head ?>`, `<?= $this->header ?>` and 
`<?= $this->footer ?>` in the layout template.

````php
<?php // module/Application/view/layout/partials/head.phtml ?>
<meta charset="utf-8">
<?php echo $this->headTitle('ZF2 '. $this->translate('Skeleton Application'))->setSeparator(' - ')->setAutoEscape(false) ?>

<?php echo $this->headMeta()
    ->appendName('viewport', 'width=device-width, initial-scale=1.0')
    ->appendHttpEquiv('X-UA-Compatible', 'IE=edge')
?>

<!-- Le styles -->
<?php echo $this->headLink(array('rel' => 'shortcut icon', 'type' => 'image/vnd.microsoft.icon', 'href' => $this->basePath() . '/img/favicon.ico'))
                ->prependStylesheet($this->basePath('css/style.css'))
                ->prependStylesheet($this->basePath('css/bootstrap-theme.min.css'))
                ->prependStylesheet($this->basePath('css/bootstrap.min.css')) ?>

<!-- Scripts -->
<?php echo $this->headScript()
    ->prependFile($this->basePath('js/bootstrap.min.js'))
    ->prependFile($this->basePath('js/jquery.min.js'))
    ->prependFile($this->basePath('js/respond.min.js'), 'text/javascript', array('conditional' => 'lt IE 9',))
    ->prependFile($this->basePath('js/html5shiv.js'),   'text/javascript', array('conditional' => 'lt IE 9',));
````

````php
<?php // module/Application/view/layout/partials/header.phtml ?>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo $this->url('home') ?>"><img src="<?php echo $this->basePath('img/zf2-logo.png') ?>" alt="Zend Framework 2"/>&nbsp;<?php echo $this->translate('Skeleton Application') ?></a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?php echo $this->url('home') ?>"><?php echo $this->translate('Home') ?></a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
````

````php
<?php // module/Application/view/layout/partials/footer.phtml ?>
<hr>
<footer>
    <p>&copy; 2005 - <?php echo date('Y') ?> by Zend Technologies Ltd. <?php echo $this->translate('All rights reserved.') ?></p>
</footer>
````

the new layout.phtml:

````php
<?php echo $this->doctype(); ?>

<html lang="en">
    <head>
        <?= $this->head ?>
    </head>
    <body>
        <?= $this->header ?>
        <div class="container">
            <?php echo $this->content; ?>
            <?= $this->footer ?>
        </div> <!-- /container -->
        <?php echo $this->inlineScript() ?>
    </body>
</html>
````

and the new 2cols-left.phtml

````php
<?php echo $this->doctype(); ?>

<html lang="en">
    <head>
        <?= $this->head ?>
    </head>
    <body>
        <?= $this->header ?>
        <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <h3>Sidebar</h3>
                </div>
                <div class="col-sm-9">
                    <?php echo $this->content; ?>
                </div>
            </div>
            <?= $this->footer ?>
        </div> <!-- /container -->
        <?php echo $this->inlineScript() ?>
    </body>
</html>
````

We now have to tell the layout where to place our partials/blocks.

We use the handle `default` because we want it to be valid on each request.

Create `Application/layout/default.php` and add these `blocks` layout instructions:

````php
// module/Application/layout/default.php
return [
    'blocks' => [
        // unique block id
        'head' => [
            'template' => 'layout/partials/head',
            /* <?= $this->head ?> in layout */
            'capture_to' => 'head',
            'options' => [
                'order' => 10000
            ]
        ],
        // unique block id
        'header' => [
            'template' => 'layout/partials/header',
            /* <?= $this->header ?> in layout */
            'capture_to' => 'header'
        ],
        // unique block id
        'footer' => [
            'template' => 'layout/partials/footer',
            /* <?= $this->footer ?> in layout */
            'capture_to' => 'footer'
        ]
    ]
];
````

The layout should now be displayed as before and the blocks now are listed in zdt:

![zdt-blocks-header-footer](https://dl.dropboxusercontent.com/u/4741060/conlayout/tutorial/zdt-footer-header-blocks.png)

Note: The head block has a high sort order because we have to be sure that it
will be rendered last. The reason is, that if the head would be rendered before
e.g. the content view model (the view model we return from our controller action) 
we were no longer able to modify the view helpers (alter title, add stylesheets 
...) in a template since `<head>` is already rendered.

## 6. Place a widget block in the sidebar

Because we only defined a two column layout including a sidebar for the 
`application/index/view` handle we should use the layout update file 
`application/index/view.php` to add our `blocks` layout instructions to add a 
widget to the sidebar:

````php
<?php
// module/Application/layout/application/index/view.php
return [
    'layout' => 'layout/2cols-left',
    'blocks' => [
        'my.widget' => [
            'template' => 'sidebar/widget.phtml',
            'capture_to' => 'sidebar'
        ]
    ]
];
````

Create a template for the widget block:

````php
<?php // module/Application/view/sidebar/widget.phtml ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= $this->title ? $this->title : 'Default title' ?>
        </h3>
    </div>
    <div class="panel-body">
        <?= $this->body ? $this->body : 'Default body text' ?>
    </div>
</div>
````

Go to http://your.zf2-skeleton.app/application/index/view. You should see...
nothing.

We have to alter our `2cols-left.phtml` template and add the `sidebar` 
placeholder as follows:

````php
<?php // module/Application/view/layout/2cols-left.phtml ?>
<?php echo $this->doctype(); ?>

<html lang="en">
    <head>
       <?= $this->head ?>
    </head>
    <body>
        <?= $this->header ?>
        <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <h3>Sidebar</h3>
                    <?php // add the sidebar placeholder ?>
                    <?= $this->sidebar ?>
                </div>
                <div class="col-sm-9">
                    <?php echo $this->content; ?>
                </div>
            </div>
            <?= $this->footer ?>
        </div> <!-- /container -->
        <?php echo $this->inlineScript() ?>
    </body>
</html>
````

As a result we get:

![widget-1](https://dl.dropboxusercontent.com/u/4741060/conlayout/tutorial/widget-1.png)

## 7. Create a custom block and add some logic to it

Create a new block class in 
`module/Application/src/Application/Block/LatestArticles.php` with the 
following content:

````php
<?php
// module/Application/src/Application/Block/LatestArticles.php

namespace Application\Block;

use ConLayout\Block\AbstractBlock;
use DateTime;

class LatestArticles extends AbstractBlock
{
    protected $template = 'sidebar/latest-articles';

    public function getTitle()
    {
        if (!$title = $this->getVariable('title')) {
            $title = 'Latest articles';
        }
        return $title;
    }

    public function getArticles($limit = 5)
    {
        $articles = [];
        for ($i = 1; $i <= $limit; $i++) {
            $articles[] = [
                'title' => 'Article title ' . $i,
                'content' => 'Lorem ipsum dolor sit amet',
                'date' => new DateTime()
            ];
        }
        return $articles;
    }
}
````

Add a new template `sidebar/latest-articles.phtml` 

````php
<?php // module/Application/view/sidebar/latest-articles.phtml ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php // call the block's getTitle method ?>
            <?= $this->escapeHtml($this->getTitle()) ?>
        </h3>
    </div>
    <div class="panel-body">
        <ul class="list-unstyled">
        <?php // call the block's getArticles() method ?>
        <?php foreach ($this->getArticles(3) as $article): ?>
            <li>
                <h4><?= $this->escapeHtml($article['title']) ?></h4>
                <p><?= $this->escapeHtml($article['content']) ?></p>
                <small>Date: <?= $this->dateFormat($article['date'], IntlDateFormatter::MEDIUM) ?></small>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>

````

Edit the `application/index/view.php` file:

````php
<?php
// module/Application/layout/application/index/view.php
return [
    'layout' => 'layout/2cols-left',
    'blocks' => [
        'latest.articles' => [
            'class' => 'Application\Block\LatestArticles',
            'capture_to' => 'sidebar'
        ]
    ]
];
````

We do not need to include the template in the instructions since we defined one
in our block class. 

Result with our custom block should look like this:

![widget-latest-articles](https://dl.dropboxusercontent.com/u/4741060/conlayout/tutorial/widget-latest-articles.png)

## 8. Include a handle

Sometimes we want to reuse a defined layout instruction in a different handle.
For this purpose there is an instruction called `include`.

To demonstrate this, add one more action to the 
`Application\Controller\IndexController`:

````php
// module/Application/src/Application/Controller/IndexController.php

class IndexController extends AbstractActionController
{
    // ... other actions ...

    public function includeAction()
    {
        return new ViewModel();
    }
}
````

If you now go to http://your.zf2-skeleton.app/application/index/include the
layout structure should be the same as defined in our `default.php` since the
default-handle is the only one that matches in this request.

Create a new layout update file for the handle 
`application/index/include` with the following content:

````php
<?php
return [
    'include' => [
        'application/index/view'
    ]
];
````

Reload your browser and et voilà! Your layout now has the same structure as the
view action.

## 9. Remove blocks from a specific page

We can remove blocks with the instruction `remove_blocks`

As an example, we edit our layout update file for the handle 
`application/index/include`:

````php
return [
    'include' => [
        'application/index/view'
    ],
    'remove_blocks' => [
        'latest.articles' => true
    ]
];
````

The widget should now be disappeared.