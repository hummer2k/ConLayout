<?php

declare(strict_types=1);

namespace ConLayout\Inflector;

use Laminas\Stdlib\StringUtils;

class TemplateInflector
{
    /**
     * Array of controller namespace -> template mappings.
     */
    protected array $controllerMap = [];

    /**
     * Flag to force the use of the route match controller param.
     */
    protected bool $preferRouteMatchController = false;

    public function setControllerMap(array $map): static
    {
        krsort($map);
        $this->controllerMap = $map;
        return $this;
    }

    public function isPreferRouteMatchController(): bool
    {
        return $this->preferRouteMatchController;
    }

    public function setPreferRouteMatchController(bool $flag): static
    {
        $this->preferRouteMatchController = $flag;
        return $this;
    }

    /**
     * Maps a controller FQCN to a template name.
     */
    public function mapController(string $controller): string
    {
        $mapped = '';
        foreach ($this->controllerMap as $namespace => $replacement) {
            if (
                false == $replacement
                || !($controller === $namespace || str_starts_with($controller, $namespace . '\\'))
            ) {
                continue;
            }

            if (is_string($replacement)) {
                $mapped = rtrim($replacement, '/') . '/';
                $controller = substr($controller, strlen($namespace) + 1) ?: '';
                break;
            }
        }

        $parts = explode('\\', $controller);
        array_pop($parts);
        $parts = array_diff($parts, ['Controller']);
        $parts[] = $this->deriveControllerClass($controller);
        $controller = implode('/', $parts);

        $template = trim($mapped . $controller, '/');

        return $this->inflectName($template);
    }

    /**
     * Inflect a name to a normalized, dash-separated lowercase value.
     */
    public function inflectName(string $name): string
    {
        if (StringUtils::hasPcreUnicodeSupport()) {
            $pattern     = ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'];
            $replacement = ['-\1', '-\1'];
        } else {
            $pattern     = ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'];
            $replacement = ['\1-\2', '-\1'];
        }

        $name = preg_replace($pattern, $replacement, $name);
        return strtolower((string) $name);
    }

    /**
     * Strip the namespace, and the suffix "Controller" if present.
     */
    protected function deriveControllerClass(string $controller): string
    {
        if (str_contains($controller, '\\')) {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }

        if (
            (10 < strlen($controller))
            && (str_ends_with($controller, 'Controller'))
        ) {
            $controller = substr($controller, 0, -10);
        }

        return $controller;
    }
}
