<?php
namespace ConLayout\ValuePreparer;

use Zend\ServiceManager\FactoryInterface;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePathFactory implements FactoryInterface
{
    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $basePathHelper = $serviceLocator->get('viewHelperManager')
            ->get('basePath');
        return new BasePath($basePathHelper);
    }
}
