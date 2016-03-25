<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ViewHelperGeneratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, ViewHelperGenerator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return ViewHelperGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ModuleOptions $options */
        $options             = $container->get(ModuleOptions::class);
        $filterPluginManager = $container->get('FilterManager');
        $viewHelperManager   = $container->get('ViewHelperManager');
        $helperConfig        = $options->getViewHelpers();
        $viewHelperGenerator = new ViewHelperGenerator(
            $filterPluginManager,
            $viewHelperManager,
            $helperConfig
        );
        $viewHelperGenerator->setDebug($options->isDebug());
        return $viewHelperGenerator;
    }
}
