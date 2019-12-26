<?php

namespace ConLayoutTest\Filter;

use ConLayout\Filter\BasePathFilter as BasePathFilter;
use ConLayout\Filter\BasePathFilter as BasePathFilter2;
use ConLayout\Filter\BasePathFilterFactory;
use ConLayoutTest\AbstractTest;
use Laminas\Filter\FilterInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\BasePath;
use Laminas\View\HelperPluginManager;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePathFilterTest extends AbstractTest
{
    private $basePathFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $basePathHelper = new BasePath();
        $basePathHelper->setBasePath('/my/base/');

        $this->basePathFilter = new BasePathFilter(
            $basePathHelper
        );
    }

    public function testFactory()
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

    public function testBasePathLocal()
    {
        $value  = '/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals('/my/base/css/styles.css', $result);
    }

    public function testBasePathRemote()
    {
        $value  = '//cdn.example.com/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals($value, $result);
    }

    public function testBasePathHttp()
    {
        $value  = 'http://cdn.example.com/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals($value, $result);
    }

    public function testBasePathHttps()
    {
        $value  = 'https://cdn.example.com/css/styles.css';
        $result = $this->basePathFilter->filter($value, $value);

        $this->assertEquals($value, $result);
    }
}
