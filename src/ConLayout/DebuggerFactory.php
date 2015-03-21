<?php

namespace ConLayout;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class DebuggerFactory implements \Zend\ServiceManager\FactoryInterface
{
    use OptionTrait;

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $debugger = new Debugger();
        $debugger->setEnabled($this->getOption($config, 'con-layout/enable_debug', false));
        return $debugger;
    }
}
