<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Listener\Factory;

use ConLayout\Layout\LayoutInterface;
use ConLayout\Listener\PrepareActionViewModelListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PrepareActionViewModelListenerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PrepareActionViewModelListener(
            $serviceLocator->get(LayoutInterface::class)
        );
    }
}
