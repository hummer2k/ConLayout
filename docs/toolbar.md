# Debug Toolbar

The debug toolbar renders a small floating panel into HTML responses. The
panel is similar to the laminas-developer-tools toolbar. The toolbar shows the
ConLayout state of the current request in four tabs:

**Handles**

- The update handles of the request (name and priority).
- For each handle, the layout update files that were applied.
- The rendered layout template (the root block template).

**Blocks**

- The rendered blocks as a nesting tree (block id, template, `capture_to`,
  `order`, `parent`, `before`/`after`, `container`, `wrapper`, `append`).
  The toolbar marks anonymous blocks.
- Blocks that are registered in the pool but not part of the rendered tree.
- Blocks that were removed.

**Structure**

- The count of the `blocks`, `reference`, `helpers`, and `include`
  instructions in the merged layout structure.
- The layout area.
- The view helpers called with the `helpers` instructions.

**Info**

- The request duration and the peak memory usage.
- A snapshot of the relevant `con-layout` module options.

## Enable

ConLayout renders the toolbar only when the application runs in development
mode. The merged `debug` config key must be `true`:

```php
return [
    'debug' => true,
];
```

Mezzio development mode does this automatically:

```bash
composer development-enable
```

## Pipeline

The `ConLayout\Toolbar\ToolbarMiddleware` is part of the `middleware` config
returned by the `ConfigProvider`. It must be the outermost middleware of the
pipeline. Then it can inject the toolbar into the final response. Pipe it
before any other middleware:

```php
$app->pipe(\ConLayout\Middleware\ToolbarMiddleware::class);
```

## Behavior

- The toolbar is skipped for non-HTML responses. It is also skipped for
  requests where no layout has been rendered. For example: JSON APIs.
- The toolbar is injected before the closing `</body>` tag. If the body does
  not have this tag, the toolbar is appended to the response.
- The data is read from the `LayoutManager` service after the request has been
  handled. So the data shows the exact handles and blocks of the current
  request.

## Customization

You can replace the toolbar template. Override the `con-layout::toolbar`
template map entry. Or pass a custom template name to the middleware.

## Worth knowing

- Use the Handles tab to check the layout update files that were applied.
  The tab shows the handles with their priority and the files for each
  handle.
- In debug mode, ConLayout adds the `data-layout-id` attribute to the
  output of the view helpers that have a `debug` config entry. The
  attribute contains the id of the `helpers` instruction. Use it to find
  the layout update file of an asset.
- The toolbar does not change the response when no layout was rendered. It
  also does not change JSON responses.
- The toolbar measures the complete request. The duration includes the
  time of all middleware.
