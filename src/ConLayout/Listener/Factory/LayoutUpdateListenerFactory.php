<?php

namespace ConLayout\Listener\Factory;

use ConLayout\Listener\LayoutUpdateListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutUpdateListenerFactory implements FactoryInterface
{
    use \ConLayout\OptionTrait;

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return LayoutUpdateListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $globPaths = $this->getOption($config, 'con-layout/update_listener/glob_paths', []);
        $layoutUpdateListener = new LayoutUpdateListener(
            $globPaths
        );
        return $layoutUpdateListener;
    }
}
