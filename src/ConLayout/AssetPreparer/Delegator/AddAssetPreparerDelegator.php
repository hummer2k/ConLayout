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
    use \ConLayout\OptionTrait;

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
        $assetPreparersConfig = $this->getOption(
            $serviceLocator->get('Config'),
            'con-layout/asset_preparers',
            []
        );
        foreach ($assetPreparersConfig as $helper => $assetPreparers) {
            if (!is_array($assetPreparers)) {
                continue;
            }
            foreach ($assetPreparers as $assetPreparer) {
                if (false === $assetPreparer) {
                    continue;
                }
                $viewHelperListener->addAssetPreparer(
                    $helper,
                    $serviceLocator->get($assetPreparer)
                );
            }
        }
        return $viewHelperListener;
    }
}
