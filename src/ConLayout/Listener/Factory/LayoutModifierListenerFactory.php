<?php
namespace ConLayout\Listener\Factory;

use ConLayout\Listener\LayoutModifierListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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

        $assetPreparers = $this->getOption($config, 'con-layout/value_preparers', array());
        $layoutModifierListener = new LayoutModifierListener(
            $serviceLocator->get('ConLayout\Service\LayoutService'),
            $serviceLocator->get('ConLayout\Service\BlocksBuilder'),
            $serviceLocator->get('ConLayout\Service\LayoutModifier'),
            $serviceLocator->get('ViewHelperManager'),
            $serviceLocator->get('ConLayout\Debugger'),
            $this->getOption($config, 'con-layout/helpers', array())
        );

        foreach ($assetPreparers as $helper => $assetPreparers) {
            foreach ($assetPreparers as $assetPreparer) {
                if (false === $assetPreparer) continue;
                $layoutModifierListener->addAssetPreparer($helper, $serviceLocator->get($assetPreparer));
            }
        }

        return $layoutModifierListener;
    }
}
