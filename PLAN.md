# PHP 8.4 Compatibility Refactor

## Findings

The entire locked dependency stack is PHP 7.x-era (`php: ^7.3`, laminas-mvc 3.2.0, PHPUnit 8.5.52, satooshi/php-coveralls + guzzle 3, Travis CI). Laminas dropped PHP 7.x: the newest 8.4-capable laminas releases all require `~8.2+`. The project's own code is mostly PHP 8-safe; the only 8.4 deprecation issues are 31 implicit-nullable parameter signatures, and 3 test assertions using PHPUnit 8-only APIs.

Decisions (user confirmed): PHP floor `^8.2`, drop coveralls entirely, migrate CI to GitHub Actions.

## 1. `composer.json` - dependency modernization (PHP ^8.2)

- `php: ^8.2`
- require: `laminas-config ^3.10`, `laminas-eventmanager ^3.14`, `laminas-http ^2.22`, `laminas-modulemanager ^2.18`, `laminas-mvc ^3.8` (last 3.x, supports 8.4), `laminas-router ^3.16`, `laminas-servicemanager ^3.20`, `laminas-stdlib ^3.20`, `laminas-view ^2.25`
- require-dev: `phpunit/phpunit ^11.5`, `squizlabs/php_codesniffer ^3.11`, `laminas-developer-tools ^2.10`, `laminas-filter ^2.42`, `laminas-i18n ^2.15`; remove `satooshi/php-coveralls`
- Regenerate `composer.lock` via `composer update`

## 2. Fix PHP 8.4 deprecations in `src/`

Implicit-nullable parameter types (deprecated in 8.4, noisy under the test bootstrap's `error_reporting(E_ALL)`):

- 22 factories: `__invoke(ContainerInterface $c, $requestedName, array $options = null)` to `?array $options = null` (matches the new `FactoryInterface` signature)
- `BlockFactory::__construct(ContainerInterface $blockManager = null, ...)` to `?ContainerInterface`
- `BlocksGenerator::generateBlocks(Config $blocks, Config $references = null, ...)` to `?Config`

All other risky patterns (dynamic props, `each()`, `create_function`, `utf8_*`, reversed `implode`, curly-brace offsets) verified absent.

## 3. Test modernization

- `test/Listener/ActionHandlesListenerTest.php`: 3x `assertContains($needle, $haystack)` to `assertStringContainsString`
- `phpunit.xml.dist`: migrate to PHPUnit 10/11 schema (`<whitelist>`/`processUncoveredFilesFromWhitelist` to `<source>` with `<include>`/`<exclude>`)
- `test/Bootstrap.php` and `TestConfig.php.dist` need no changes

## 4. CI - GitHub Actions

- New `.github/workflows/ci.yml`: PHP 8.2/8.3/8.4 matrix, `composer install`, `phpunit --coverage-clover`, `phpcs --standard=PSR12 ./src/ ./test/` (same order as before)
- Delete `.travis.yml`; update dead badges in `Readme.md` (Travis + Coveralls)

## 5. Docs

- `AGENTS.md`: drop the "use a PHP 7.x container" workaround, document new PHP floor, PHPUnit 11 and the GitHub Actions matrix

## 6. Verification

- `composer install` on the local PHP 8.4, then `./vendor/bin/phpunit` and `./vendor/bin/phpcs --standard=PSR12 ./src/ ./test/`; watch for any residual 8.4 deprecation warnings (including from `laminas-developer-tools` 2.x, whose collector API must still match `src/Ldt/Collector/LayoutCollector.php`)
