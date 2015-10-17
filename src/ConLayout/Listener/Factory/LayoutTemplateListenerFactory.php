<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\LayoutTemplateListener;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutTemplateListenerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return LayoutTemplateListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $layoutTemplateListener = new LayoutTemplateListener(
            $serviceLocator->get(LayoutUpdaterInterface::class)
        );
        return $layoutTemplateListener;
    }
}
