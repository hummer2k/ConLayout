<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ViewHelperGeneratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $filterPluginManager = $serviceLocator->get('FilterManager');
        $viewHelperManager   = $serviceLocator->get('ViewHelperManager');
        $helperConfig        = $serviceLocator->get(ModuleOptions::class)->getViewHelpers();
        $viewHelperGenerator = new ViewHelperGenerator(
            $filterPluginManager,
            $viewHelperManager,
            $helperConfig
        );
        return $viewHelperGenerator;
    }
}
