<?php

declare(strict_types=1);

namespace ConLayoutTest\Filter;

use ConLayout\Filter\BasePathFilter as BasePathFilter;
use ConLayout\Filter\BasePathFilter as BasePathFilter2;
use ConLayout\Filter\BasePathFilterFactory;
use ConLayoutTest\AbstractTestCase;
use Laminas\Filter\FilterInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\BasePath;
use Laminas\View\HelperPluginManager;

class BasePathFilterTest extends AbstractTestCase
{
    private BasePathFilter $basePathFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $basePathHelper = new BasePath();
        $basePathHelper->setBasePath('/my/base/');

        $this->basePathFilter = new BasePathFilter(
            $basePathHelper
        );
    }

    public function testFactory(): void
    {
        $factory = new BasePathFilterFactory();
        $viewHelperManager = new HelperPluginManager($this->sm);
        $viewHelperManager->setService('basePath', new BasePath());

        $serviceManager = new ServiceManager();
        $serviceManager->setService('ViewHelperManager', $viewHelperManager);

        $instance = $factory($this->sm, BasePathFilter2::class);

        $this->assertInstanceOf(
            BasePathFilter2::class,
            $instance
        );

        $this->assertInstanceOf(
            FilterInterface::class,
            $instance
        );
    }

    public function testBasePathLocal(): void
    {
        $value  = '/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals('/my/base/css/styles.css', $result);
    }

    public function testBasePathRemote(): void
    {
        $value  = '//cdn.example.com/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals($value, $result);
    }

    public function testBasePathHttp(): void
    {
        $value  = 'http://cdn.example.com/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals($value, $result);
    }

    public function testBasePathHttps(): void
    {
        $value  = 'https://cdn.example.com/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals($value, $result);
    }
}
