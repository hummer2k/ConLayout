<?php

namespace ConLayoutTest\Filter;

use ConLayout\Filter\BasePathFilter as BasePathFilter;
use ConLayout\Filter\BasePathFilterFactory;
use ConLayoutTest\AbstractTest;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\BasePath;
use Zend\View\HelperPluginManager;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class BasePathTest extends AbstractTest
{
    private $basePathFilter;

    public function setUp()
    {
        $basePathHelper = new BasePath();
        $basePathHelper->setBasePath('/my/base/');

        $this->basePathFilter = new BasePathFilter(
            $basePathHelper
        );
    }

    public function testFactory()
    {
        $factory = new BasePathFilterFactory();
        $viewHelperManager = new HelperPluginManager();
        $viewHelperManager->setService('basePath', new BasePath());

        $serviceManager = new ServiceManager();
        $serviceManager->setService('viewHelperManager', $viewHelperManager);

        $filterManager = new \Zend\Filter\FilterPluginManager();
        $filterManager->setServiceLocator($serviceManager);

        $instance = $factory->createService($filterManager);

        $this->assertInstanceOf(
            'ConLayout\Filter\BasePathFilter',
            $instance
        );

        $this->assertInstanceOf(
            'Zend\Filter\FilterInterface',
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
