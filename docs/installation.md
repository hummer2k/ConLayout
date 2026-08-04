# Installation

Install via composer:

`$ composer require hummer2k/conlayout:^5.0`

Register the `ConfigProvider` in your application. Depending on how your
Mezzio application loads configuration, either add it to the config aggregator
or merge it in `config/config.php`:

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
`config/autoload/con-layout.global.php` and adjust the paths and settings.

Finally pipe the ConLayout middleware into your application pipeline. The
middleware must be registered before routing and dispatching:

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

`RouteMiddleware` and `DispatchMiddleware` are the ones provided by your Mezzio
router (`Mezzio\Router\Middleware\RouteMiddleware` and
`Mezzio\Router\Middleware\DispatchMiddleware`).
