<?php
namespace ConLayout\Config;
use Zend\ServiceManager\FactoryInterface;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class SorterFactory implements FactoryInterface
{
    use \ConLayout\OptionTrait;
    
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \ConLayout\Config\Sorter
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $layoutManager = $serviceLocator->get('LayoutManager');
        $handles = $layoutManager->getHandles();
        $sorter = new Sorter($handles);
        return $sorter;
    }
}
