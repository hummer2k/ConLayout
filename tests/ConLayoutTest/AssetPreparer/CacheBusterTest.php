<?php

namespace ConLayoutTest\AssetPreparer;

use ConLayout\AssetPreparer\CacheBuster;
use ConLayout\AssetPreparer\CacheBusterFactory;
use ConLayoutTest\AbstractTest;
use Zend\ServiceManager\ServiceManager;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBusterTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new CacheBusterFactory();

        $serviceManager = new ServiceManager();

        $options = new \ConLayout\Options\ModuleOptions();
        $options->setCacheBusterInternalBaseDir(__DIR__ . '/_files');

        $serviceManager->setService('ConLayout\Options\ModuleOptions', $options);

        $instance = $factory->createService($serviceManager);

        $this->assertInstanceOf(
            'ConLayout\AssetPreparer\CacheBuster',
            $instance
        );
        $this->assertInstanceOf(
            'ConLayout\AssetPreparer\AssetPreparerInterface',
            $instance
        );
    }

    public function testMd5File()
    {
        $cacheBuster = new CacheBuster(
            __DIR__ . '/_files'
        );

        $value = $cacheBuster->prepare('styles.css', 'styles.css');
        $this->assertEquals('styles.css?1688c8210b6509d702b1adb96bc4d0f3', $value);
    }

    public function testFileDoesNotExist()
    {
        $cacheBuster = new CacheBuster(
                'DOES_NOT_EXIST_____'
        );
        $value = $cacheBuster->prepare('styles.css', 'styles.css');
        $this->assertEquals('styles.css', $value);
    }
}