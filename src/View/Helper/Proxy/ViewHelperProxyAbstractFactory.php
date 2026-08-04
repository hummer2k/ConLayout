<?php

declare(strict_types=1);

namespace ConLayout\View\Helper\Proxy;

use ConLayout\Options\ModuleOptions;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class ViewHelperProxyAbstractFactory implements AbstractFactoryInterface
{
    protected ?string $helperAlias = null;

    protected ?string $proxyClass = null;

    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        /* @var $moduleOptions ModuleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        foreach ($moduleOptions->getViewHelpers() as $helperAlias => $helperConfig) {
            if (
                isset($helperConfig['proxy'])
                && $helperConfig['proxy'] === $requestedName
            ) {
                $this->helperAlias = $helperAlias;
                $this->proxyClass  = $requestedName;
                return true;
            }
        }
        return false;
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): mixed
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        $helper = $viewHelperManager->get($this->helperAlias);
        $proxy  =  new $this->proxyClass($helper);
        return $proxy;
    }
}
