<?php
namespace ConLayout\Filter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePathFilterFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $basePathHelper = $serviceLocator->getServiceLocator()
            ->get('viewHelperManager')
            ->get('basePath');
        return new BasePathFilter($basePathHelper);
    }
}
