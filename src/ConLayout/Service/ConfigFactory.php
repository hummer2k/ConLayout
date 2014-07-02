<?php
namespace ConLayout\Service;

use Zend\ServiceManager\FactoryInterface,
    Zend\Mvc\Router\RouteMatch,
    Zend\Mvc\Router\RouteStackInterface as Router,
    \ConLayout\OptionTrait;

/**
 * ConfigFactory
 *
 * @author hummer 
 */
class ConfigFactory
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
        $layoutConfig = new Config(
            $serviceLocator->get('ConLayout\Service\Config\CollectorInterface'),
            $serviceLocator->get('ConLayout\Cache'),
            $serviceLocator->get('ConLayout\Service\Config\SorterInterface')
        );
        $layoutConfig->setIsCacheEnabled($enableCache);
        return $layoutConfig;
    }
}
