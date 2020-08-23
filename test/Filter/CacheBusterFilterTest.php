<?php

namespace ConLayoutTest\Filter;

use ConLayout\Filter\CacheBusterFilter;
use ConLayout\Filter\CacheBusterFilterFactory;
use ConLayout\Options\ModuleOptions;
use ConLayoutTest\AbstractTest;
use Laminas\Filter\FilterInterface;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBusterFilterTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new CacheBusterFilterFactory();

        $serviceManager = new ServiceManager();

        $options = new ModuleOptions();
        $options->setCacheBusterInternalBaseDir(__DIR__ . '/_files');

        $serviceManager->setService(ModuleOptions::class, $options);

        $filterManager = new FilterPluginManager($serviceManager);

        $instance = $factory($serviceManager, CacheBusterFilter::class);

        $this->assertInstanceOf(
            CacheBusterFilter::class,
            $instance
        );
        $this->assertInstanceOf(
            FilterInterface::class,
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
