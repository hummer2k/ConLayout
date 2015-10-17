<?php

namespace ConLayoutTest\Filter;

use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Filter\CacheBusterFilterFactory;
use ConLayout\Options\ModuleOptions;
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
        $factory = new CacheBusterFilterFactory();

        $serviceManager = new ServiceManager();

        $options = new ModuleOptions();
        $options->setCacheBusterInternalBaseDir(__DIR__ . '/_files');

        $serviceManager->setService('ConLayout\Options\ModuleOptions', $options);

        $filterManager = new \Zend\Filter\FilterPluginManager();
        $filterManager->setServiceLocator($serviceManager);

        $instance = $factory->createService($filterManager);

        $this->assertInstanceOf(
            'ConLayout\Filter\CacheBusterFilter',
            $instance
        );
        $this->assertInstanceOf(
            'Zend\Filter\FilterInterface',
            $instance
        );
    }

    public function testMd5File()
    {
        $cacheBuster = new CacheBusterFilter(
            __DIR__ . '/_files'
        );

        $value = $cacheBuster->filter('styles.css', 'styles.css');
        $this->assertEquals('styles.css?v=1688c821', $value);
    }

    public function testFileDoesNotExist()
    {
        $cacheBuster = new CacheBusterFilter(
            'DOES_NOT_EXIST_____'
        );
        $value = $cacheBuster->filter('styles.css', 'styles.css');
        $this->assertEquals('styles.css', $value);
    }

    public function testOriginalValue()
    {
        $cacheBuster = new CacheBusterFilter(__DIR__ . '/_files');
        $cacheBuster->setRawValue('original.css');
        $value = $cacheBuster->filter('test.css');
        $this->assertEquals('test.css?v=a1aa7a96', $value);
    }
}
