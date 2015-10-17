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
        $moduleOptions = $serviceLocator->getServiceLocator()
            ->get(ModuleOptions::class);

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

    public function createServiceWithName(ServiceLocatorInterface $viewHelperManager, $name, $requestedName)
    {
        $helper = $viewHelperManager->get($this->helperAlias);
        $proxy  =  new $this->proxyClass($helper);
        return $proxy;
    }
}
