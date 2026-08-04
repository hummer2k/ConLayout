# Migration: Route-name based action handles

## Scope & baseline

- `HandleExtractionMiddleware::getActionHandles()` derives handles from laminas-mvc style `controller`/`action` route params (a relic) or falls back to the matched route name. In Mezzio, a route matches one middleware/handler directly (`$app->get('/', HomePageHandler::class, 'app.home')`); `Mezzio\Router\Route` has no defaults, so `RouteResult` matched params only ever contain path segments. The `controller`/`action` path is dead code in Mezzio and the `TemplateInflector` controller map is unused.
- An earlier approach derived handles from the matched handler class (FQCN) and required an exclusion list for framework namespace segments (`Handler`, `Middleware`, `TestAsset`). Rejected: the exclusion list grows with every new namespace layer and handle naming is not directly controllable.
- New approach: handles derive from the route name itself, which the developer controls at route registration. No class inspection, no namespace conventions. Mezzio route names conventionally use dots as separator (e.g. `api.ping`), so the route name is split on `.` and handles are built from the segments.
- `ConLayout\Inflector\TemplateInflector` was a copy of the laminas-mvc `InjectTemplateListener` inflection logic. The identical camelCase-to-dash logic already ships in `Laminas\Filter\Word\CamelCaseToSeparator`/`CamelCaseToDash` (laminas-filter is a required dependency and already used by the package filter chain). Verified behaviorally identical, `inflectName()` == `strtolower(CamelCaseToDash->filter())`. The copy is dropped instead of being carried on.

## Decisions

1. **Drop the legacy path**: the `controller`/`action` params and the `action` template suffix are removed. Handler-class extraction is not introduced.
2. **Route name is the single source of truth**: handles are built from the matched route name. The whole name is inflected once (laminas-filter `CamelCaseToDash` plus `strtolower`; lowercase, dash-separated; dots pass through), then split on the route-name separator `.`. One cumulative handle per segment, joined with `/`, priority 10, 20, 30...
3. **Naming convention**: register routes with dot-separated names, e.g. `app.home`, `customer.account.dashboard`. A route `app.home` yields the handles `app` (priority 10) and `app/home` (priority 20); `customer.account.dashboard` yields `customer` (10), `customer/account` (20) and `customer/account/dashboard` (30). Layout update files are matched per handle as `layout/{handle}.xml`, i.e. `layout/app.xml` and `layout/app/home.xml`.
4. `/` never splits: a slash inside a route name stays part of one segment.
5. **Error/route-failure paths** (`error-router-no-match`, error attribute) stay untouched.
6. **No duplicated laminas code**: `TemplateInflector` (copy of laminas-mvc `InjectTemplateListener` logic) is removed; inflection uses the existing laminas-filter `CamelCaseToDash` plus `strtolower`.

## Implementation

### `src/Middleware/HandleExtractionMiddleware.php`

- Constructor takes `Laminas\Filter\Word\CamelCaseToDash` instead of `ConLayout\Inflector\TemplateInflector`.
- Private helper `inflectName(string $name): string` returns `strtolower((string) $this->inflector->filter($name))`; used for the route name and the error short name.
- Rework the private method (renamed from `getActionHandles()` to `getRouteHandles(RouteResult $routeResult)`):

```
1. template = inflectName((string) routeResult->getMatchedRouteName())
2. split template on self::ROUTE_NAME_SEPARATOR ('.'), emit one cumulative Handle per part joined with self::SEPARATOR ('/'), priority += 10 each
```

- Remove the `controller`/`action` reads and the `action` append.
- `self::SEPARATOR` (`/`) stays as the handle/join separator; add `self::ROUTE_NAME_SEPARATOR = '.'` as the route-name split marker.
- Error and route-failure branches in `injectHandles()` are not touched.

### `src/Middleware/HandleExtractionMiddlewareFactory.php`

- Drop the `controller_map`/`prefer_route_match_controller` wiring, inject `new CamelCaseToDash()`.

### Removals

- Delete `src/Inflector/TemplateInflector.php` (and the empty `src/Inflector/` directory).
- `src/Options/ModuleOptions.php`: remove the dead `controllerMap` and `preferRouteMatchController` properties and their accessors (no consumers remain, no config key exists).
- `CHANGELOG.md`: drop the v5.0.0 `TemplateInflector` "Added" entry, note the switch to laminas-filter `CamelCaseToDash` and the removed options.

### Tests

`test/Middleware/HandleExtractionMiddlewareTest.php`:

- Construct the middleware with `new CamelCaseToDash()`.
- Remove `testApplicationIndexIndex` and `testApplicationIndexIndexWithControllerMap` (they cover the removed `controller`/`action` path).
- Keep `testRouteNameBasedHandles` with a dot-separated route name (`blog.post` yields `blog`, `blog/post`), `testInjectErrorHandleFromString`, `testInjectErrorHandleFromThrowable`, `testInjectErrorHandleOnRouteFailure`.
- Add `testPrefixedRouteNameBasedHandles`: `app.home` yields `app` (10) and `app/home` (20).
- Add `testMultiSegmentRouteNameBasedHandles`: `customer.account.dashboard` yields `customer` (10), `customer/account` (20) and `customer/account/dashboard` (30).
- Add `testCamelCaseRouteNameBasedHandles`: `app.homePage` yields `app` (10) and `app/home-page` (20), covering the dash inflection after the dependency swap.

`test/Options/ModuleOptionsTest.php`:

- Remove the controller-map and prefer-route-match-controller assertions.

## App demo (optional)

- Rename the home route in `config/routes.php`: `$app->get('/', HomePageHandler::class, 'app.home')`.
- Add `src/App/view/layout/app.xml` and `src/App/view/layout/app/home.xml` with a small handle-scoped block. Layout updates are collected from `src/App/view/layout` (area `global`, see `src/App/src/ConfigProvider.php`).
- Verify in the conlayout toolbar that the home request shows the handles `app`, `app/home` instead of `home`.

## Verification

- Package: `../../../vendor/bin/phpunit -c phpunit.xml.dist` and `../../../vendor/bin/phpcs --standard=PSR12 ./src/ ./test/` from `vendor/hummer2k/conlayout/`.
- App: `composer test`, then a browser/toolbar check via `composer serve` (or ddev).

## Open points

- Route names are plain map keys for `url_for()` generation; a dot-separated name like `app.home` does not collide with URI paths and matches the Mezzio skeleton naming style.
- A `/` inside a route name (legacy style) stays a single handle; migrate such names to dot-separated ones if per-segment handles are wanted.
