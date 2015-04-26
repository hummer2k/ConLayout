<?php

namespace ConLayoutTest\AssetPreparer\Delegator;

use ConLayout\AssetPreparer\Delegator\AddAssetPreparerDelegator;
use ConLayout\Listener\ViewHelperListener;
use ConLayout\Options\ModuleOptions;
use ConLayoutTest\AbstractTest;
use ReflectionClass;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class AddAssetPreparerDelegatorTest extends AbstractTest
{
    public function testFactory()
    {
        $delegator = new AddAssetPreparerDelegator();

        $serviceLocator = new ServiceManager();
        $serviceLocator->setService('ConLayout\Options\ModuleOptions', new ModuleOptions([
            'asset_preparers' => [
                'headLink' => [
                    'ConLayout\AssetPreparer\BasePath' => false
                ]
            ]
        ]));

        $listener = $delegator->createDelegatorWithName(
            $serviceLocator,
            'name',
            'name',
            function () {
                return new ViewHelperListener(
                    $this->layoutUpdater,
                    new HelperPluginManager(),
                    [
                        'headLink' => []
                    ]
                );
            }
        );

        $reflection = new ReflectionClass($listener);
        $assetPreparers = $reflection->getProperty('assetPreparers');
        $assetPreparers->setAccessible(true);

        $this->assertCount(0, $assetPreparers->getValue($listener));
    }
}
