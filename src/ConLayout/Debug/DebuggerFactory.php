<?php

namespace ConLayout\Debug;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class DebuggerFactory implements FactoryInterface
{
    use OptionTrait;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $debugger = new Debugger();
        $debugger->setEnabled($this->getOption($config, 'con-layout/enable_debug', false));
        return $debugger;
    }
}
