# ConLayout

[![CI](https://github.com/hummer2k/ConLayout/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/hummer2k/ConLayout/actions/workflows/ci.yml)

Mezzio package for handling layouts with blocks and handles similar to
Magento. Layouts are defined in XML layout update files that are resolved per
request handles. Blocks are `Laminas\View\Model\ViewModel` instances that are
rendered as a view model tree by the `PhpRenderer`.

## Installation

Install via composer:

`$ composer require hummer2k/conlayout:^5.0`

Register `ConLayout\ConfigProvider` in your config aggregator, copy
`vendor/hummer2k/conlayout/config/con-layout.global.php.dist` to
`config/autoload/con-layout.global.php` and pipe the middleware into your
application pipeline.

See [docs/installation.md](docs/installation.md) and
[docs/integration.md](docs/integration.md) for details.
