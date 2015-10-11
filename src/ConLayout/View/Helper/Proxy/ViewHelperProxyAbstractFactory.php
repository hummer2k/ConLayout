<?php

namespace ConLayout\View\Helper\Proxy;

use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperProxyAbstractFactory implements AbstractFactoryInterface
{
    protected $viewHelperConfig;

    /**
     *
     * @var string
     */
    protected $helperAlias;

    /**
     *
     * @var string
     */
    protected $proxyClass;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /* @var $moduleOptions ModuleOptions */
        $moduleOptions = $serviceLocator->get('ConLayout\Options\ModuleOptions');

        foreach ($moduleOptions->getViewHelpers() as $helperAlias => $helperConfig) {
            if (isset($helperConfig['proxy']) &&
                $helperConfig['proxy'] === $requestedName
            ) {
                $this->helperAlias = $helperAlias;
                $this->proxyClass  = $requestedName;
                return true;
            }
        }
        return false;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $proxyClass = $this->proxyClass;
        return new $proxyClass(
            $serviceLocator->get('ViewHelperManager')->get($this->helperAlias)
        );
    }
}
