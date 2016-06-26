<?php

namespace ConLayout\View\Helper\Proxy;

use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ViewHelperProxyAbstractFactory implements AbstractFactoryInterface
{
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

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        /* @var $moduleOptions ModuleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

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

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        $helper = $viewHelperManager->get($this->helperAlias);
        $proxy  =  new $this->proxyClass($helper);
        return $proxy;
    }
}
