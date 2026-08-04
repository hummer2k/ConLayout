<?php

declare(strict_types=1);

namespace ConLayout;

use ConLayout\Block\Container;
use ConLayout\Middleware\BodyClassMiddleware;
use ConLayout\Middleware\HandleExtractionMiddleware;
use ConLayout\Middleware\LayoutContextMiddleware;
use ConLayout\Middleware\LayoutMiddleware;
use ConLayout\Middleware\PrepareMainContentMiddleware;
use ConLayout\Toolbar\ToolbarMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'blocks'       => $this->getBlockConfig(),
            'con-layout'   => $this->getConLayoutDefaults(),
            'templates'    => $this->getTemplates(),
            'view_helpers' => $this->getViewHelpers(),
            'middleware'   => $this->getMiddleware(),
        ];
    }

    public function getDependencies(): array
    {
        $dependencies = include __DIR__ . '/../config/service.config.php';
        $dependencies = array_merge_recursive(
            $dependencies,
            include __DIR__ . '/../config/filter.config.php',
        );
        return $dependencies;
    }

    public function getBlockConfig(): array
    {
        return [
            'invokables' => [
                'container' => Container::class,
            ],
            'shared' => [
                'container' => false,
            ],
        ];
    }

    public function getConLayoutDefaults(): array
    {
        $config = include __DIR__ . '/../config/con-layout.global.php.dist';
        return $config['con-layout'];
    }

    public function getTemplates(): array
    {
        $viewPath = __DIR__ . '/../view';
        return [
            'paths' => [
                $viewPath,
            ],
            'map' => [
                'con-layout::container'      => $viewPath . '/container.phtml',
                'con-layout::blocks/wrapper' => $viewPath . '/blocks/wrapper.phtml',
                'con-layout::content'        => $viewPath . '/blocks/content.phtml',
                'con-layout::toolbar'        => $viewPath . '/toolbar.phtml',
            ],
        ];
    }

    public function getViewHelpers(): array
    {
        return include __DIR__ . '/../config/viewhelper.config.php';
    }

    public function getMiddleware(): array
    {
        return [
            ToolbarMiddleware::class,
            LayoutContextMiddleware::class,
            LayoutMiddleware::class,
            HandleExtractionMiddleware::class,
            BodyClassMiddleware::class,
            PrepareMainContentMiddleware::class,
        ];
    }
}
