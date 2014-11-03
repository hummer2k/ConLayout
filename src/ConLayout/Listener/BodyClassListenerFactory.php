<?php
namespace ConLayout\Listener;

use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;


/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListenerFactory
    implements FactoryInterface
{
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return BodyClassListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $bodyClassHelper = $serviceLocator->get('viewHelperManager')->get('bodyClass');
        return new BodyClassListener(
            $bodyClassHelper
        );
    }
}
