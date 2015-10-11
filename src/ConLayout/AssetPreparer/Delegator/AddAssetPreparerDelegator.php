<?php

namespace ConLayout\AssetPreparer\Delegator;

use ConLayout\Listener\ViewHelperListener;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class AddAssetPreparerDelegator implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createDelegatorWithName(
        ServiceLocatorInterface $serviceLocator,
        $name,
        $requestedName,
        $callback
    ) {
        /* @var $viewHelperListener ViewHelperListener */
        $viewHelperListener = $callback();
        $moduleOptions = $serviceLocator->get('ConLayout\Options\ModuleOptions');
        $assetPreparersConfig = $moduleOptions->getAssetPreparers();
        foreach ($assetPreparersConfig as $helper => $assetPreparers) {
            foreach ((array) $assetPreparers as $assetPreparerName => $assetPreparer) {
                if (!$assetPreparer) {
                    continue;
                }
                $viewHelperListener->addAssetPreparer(
                    $helper,
                    $serviceLocator->get($assetPreparer),
                    $assetPreparerName
                );
            }
        }
        return $viewHelperListener;
    }
}
