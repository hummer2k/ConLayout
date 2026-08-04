# Debug Toolbar

The debug toolbar renders a small floating panel (similar to laminas-developer-tools) into HTML responses.
It shows the ConLayout state of the current request in four tabs:

**Handles**

- the update handles of the request (name and priority),
- for each handle, the layout update files that were applied,
- the rendered layout template (the root block template).

**Blocks**

- the rendered blocks as a nesting tree (block id, template, `capture_to`, `order`, `parent`, `before`/`after`, `container`, `wrapper`, `append`; anonymous blocks are marked),
- blocks that are registered in the pool but not part of the rendered tree,
- blocks that were removed.

**Structure**

- counts of the `blocks`, `reference`, `helpers` and `include` instructions in the merged layout structure,
- the layout area,
- the view helpers invoked via the helpers instructions.

**Info**

- the request duration and peak memory usage,
- a snapshot of the relevant `con-layout` module options.

## Enable

The toolbar is only rendered when the application runs in development mode,
i.e. the merged `debug` config key is `true`:

```php
return [
    'debug' => true,
];
```

The mezzio development mode does this automatically:

```bash
composer development-enable
```

## Pipeline

The `ConLayout\Toolbar\ToolbarMiddleware` is part of the `middleware` config
returned by the `ConfigProvider`. It must be the outermost middleware of the
pipeline so that it can inject the toolbar into the final response. Pipe it
before any other middleware:

```php
$app->pipe(\ConLayout\Middleware\ToolbarMiddleware::class);
```

## Behaviour

- The toolbar is skipped for non-HTML responses and for requests where no
  layout has been rendered (e.g. JSON APIs).
- The toolbar is injected before the closing `</body>` tag; if the body has
  none, it is appended to the response.
- The data is read from the `LayoutManager` service after the request has been
  handled, so it reflects exactly the handles and blocks of the current request.

## Customisation

The toolbar template can be replaced by overriding the `con-layout::toolbar`
template map entry, or by passing a custom template name to the middleware.
