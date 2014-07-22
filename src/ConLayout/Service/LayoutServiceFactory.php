<?php
namespace ConLayout\Service;

use Zend\ServiceManager\FactoryInterface,
    \ConLayout\OptionTrait;

/**
 * ConfigFactory
 *
 * @author hummer 
 */
class LayoutServiceFactory
    implements FactoryInterface
{
    use OptionTrait;
    
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\Config
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('Config');  
        $enableCache = $this->getOption($config, 'con-layout/enable_cache', false);
        $layoutService = new LayoutService(
            $serviceLocator->get('ConLayout\Service\Config\CollectorInterface'),
            $serviceLocator->get('ConLayout\Cache'),
            $serviceLocator->get('ConLayout\Service\Config\SorterInterface')
        );
        $layoutService->setIsCacheEnabled($enableCache);
        return $layoutService;
    }
}
