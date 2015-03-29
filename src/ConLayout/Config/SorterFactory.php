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
        $config = $serviceLocator->get('Config');
        $priorities = $this->getOption($config, 'con-layout/sorter/priorities', array());
        $sorter = new Sorter($priorities);
        return $sorter;
    }
}
