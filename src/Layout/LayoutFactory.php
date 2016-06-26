<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Layout;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Interop\Container\ContainerInterface;
use Zend\Mvc\View\Http\ViewManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ConLayout\Options\ModuleOptions;

class LayoutFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return Layout
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $layout = new Layout(
            $container->get(LayoutUpdaterInterface::class),
            $container->get(BlockPoolInterface::class)
        );
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);
        foreach ($moduleOptions->getGenerators() as $name => $specs) {
            $generator = $container->get($specs['class']);
            $layout->attachGenerator($name, $generator, $specs['priority']);
        }
        return $layout;
    }
}
