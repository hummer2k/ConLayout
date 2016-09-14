<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use ConLayout\Options\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ViewHelperGeneratorFactory implements FactoryInterface
{
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
        $viewHelperManager   = $container->get('ViewHelperManager');
        $helperConfig        = $options->getViewHelpers();
        $viewHelperGenerator = new ViewHelperGenerator(
            $viewHelperManager,
            $helperConfig
        );

        if ($container->has('FilterManager')) {
            $viewHelperGenerator->setFilterManager($container->get('FilterManager'));
        }

        $viewHelperGenerator->setDebug($options->isDebug());

        return $viewHelperGenerator;
    }
}
