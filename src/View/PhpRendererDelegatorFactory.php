<?php

declare(strict_types=1);

namespace ConLayout\View;

use ConLayout\Filter\ContainerFilter;
use ConLayout\Filter\DebugFilter;
use ConLayout\Options\ModuleOptions;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class PhpRendererDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null,
    ): PhpRenderer {
        $renderer = $callback();
        if (!$renderer instanceof PhpRenderer) {
            return $renderer;
        }

        $filterChain = $renderer->getFilterChain();
        $filterChain->attach(new ContainerFilter($renderer), 20);

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        if ($moduleOptions->isDebug()) {
            $filterChain->attach(new DebugFilter($renderer), 10);
        }

        return $renderer;
    }
}
