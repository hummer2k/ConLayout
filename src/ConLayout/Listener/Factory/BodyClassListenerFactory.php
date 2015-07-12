<?php
namespace ConLayout\Listener\Factory;

use ConLayout\Listener\BodyClassListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BodyClassListenerFactory implements FactoryInterface
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
