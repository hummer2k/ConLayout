<?php
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Layout;

use ConLayout\Block\BlockPoolInterface;
use ConLayout\Updater\LayoutUpdaterInterface;
use Zend\Mvc\View\Http\ViewManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ConLayout\Options\ModuleOptions;

class LayoutFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Layout
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $layout = new Layout(
            $serviceLocator->get(LayoutUpdaterInterface::class),
            $serviceLocator->get(BlockPoolInterface::class)
        );
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);
        foreach ($moduleOptions->getGenerators() as $name => $specs) {
            $generator = $serviceLocator->get($specs['class']);
            $layout->attachGenerator($name, $generator, $specs['priority']);
        }
        return $layout;
    }
}
