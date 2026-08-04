# Installation

Install with Composer:

`$ composer require hummer2k/conlayout:^5.0`

Register the `ConfigProvider` in your application. Add it to the config
aggregator, or merge it in `config/config.php`. The method depends on how your
Mezzio application loads configuration:

````php
<?php
$aggregator = new ConfigAggregator([
    \Mezzio\LaminasView\ConfigProvider::class,
    \ConLayout\ConfigProvider::class, // <--
    \App\ConfigProvider::class,
    // ...
], $cacheConfig);
````

Copy `vendor/hummer2k/conlayout/config/con-layout.global.php.dist` to
`config/autoload/con-layout.global.php`. Then adjust the paths and settings.

Then pipe the ConLayout middleware into your application pipeline. Register
the middleware before routing and dispatching:

````php
<?php
// config/pipeline.php
$app->pipe(\ConLayout\Middleware\LayoutContextMiddleware::class);
$app->pipe(\ConLayout\Middleware\LayoutMiddleware::class);

$app->pipe(RouteMiddleware::class);

$app->pipe(\ConLayout\Middleware\HandleExtractionMiddleware::class);
$app->pipe(\ConLayout\Middleware\BodyClassMiddleware::class);
$app->pipe(\ConLayout\Middleware\PrepareMainContentMiddleware::class);

$app->pipe(DispatchMiddleware::class);
````

`RouteMiddleware` and `DispatchMiddleware` come from your Mezzio router. They
are `Mezzio\Router\Middleware\RouteMiddleware` and
`Mezzio\Router\Middleware\DispatchMiddleware`.

## Layout template

ConLayout renders the root block with a layout template. Set the template
name with the `con-layout.layout_template` option:

```php
return [
    'con-layout' => [
        'layout_template' => 'layout::default',
    ],
];
```

When the option is empty, ConLayout uses the `templates.layout` config
value of Mezzio. The template must exist in the template paths or in the
template map of your application.

## Options that you should know

These options are part of the `con-layout` config key:

- `enabled`: The global switch for the layout rendering. The default is
  `true`. Set it to `false` to pass all responses through unchanged.
- `debug`: The switch for the debug toolbar and the debug filter. The
  default is `false`. Use it only in development mode.
- `layout_update_extensions`: The file extensions that ConLayout reads for
  layout updates. The default is `xml`. You can add `php` or `yaml`.
- `layout_update_paths`: The paths where ConLayout searches for layout
  update files. Group the paths by area. ConLayout always searches the
  `global` area.
- `default_area`: The area that ConLayout uses when no other area is set.
  The default is `frontend`.

The file
`vendor/hummer2k/conlayout/config/con-layout.global.php.dist` contains the
full list of options with examples.
