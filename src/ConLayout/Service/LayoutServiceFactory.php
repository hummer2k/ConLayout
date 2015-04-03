<?php
namespace ConLayout\Service;

use ConLayout\OptionTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Service\LayoutService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->get('Config');  
        $enableCache = $this->getOption($config, 'con-layout/enable_layout_cache', false);
        $cache = $this->getOption($config, 'con-layout/layout_cache', 'ConLayout\Cache');
        $layoutService = new LayoutService(
            $serviceLocator->get('ConLayout\Config\CollectorInterface'),
            $serviceLocator->get($cache),
            $serviceLocator->get('ConLayout\Config\SorterInterface')
        );
        $layoutService->setIsCacheEnabled($enableCache);

        foreach ($this->getOption($config, 'con-layout/block_config_mutators', array()) as $blockConfigModifier) {
            $layoutService->addBlockConfigMutator($serviceLocator->get($blockConfigModifier));
        }

        return $layoutService;
    }
}
