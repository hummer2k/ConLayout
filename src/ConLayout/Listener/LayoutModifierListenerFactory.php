<?php
namespace ConLayout\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class LayoutModifierListenerFactory
    implements FactoryInterface
{
    use \ConLayout\OptionTrait;
    
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return LayoutModifierListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $valuePreparers = $this->getOption($config, 'con-layout/value_preparers', array());
        $layoutModifierListener = new LayoutModifierListener(
            $serviceLocator->get('ConLayout\Service\LayoutService'),
            $serviceLocator->get('ConLayout\Service\BlocksBuilder'),
            $serviceLocator->get('ConLayout\Service\LayoutModifier'),
            $serviceLocator->get('ViewHelperManager'),
            $serviceLocator->get('ConLayout\Debugger'),
            $this->getOption($config, 'con-layout/helpers', array())
        );

        foreach ($valuePreparers as $helper => $valuePreparers) {
            foreach ($valuePreparers as $valuePreparer) {
                if (false === $valuePreparer) continue;
                $layoutModifierListener->addValuePreparer($helper, $serviceLocator->get($valuePreparer));
            }
        }

        return $layoutModifierListener;
    }
}
