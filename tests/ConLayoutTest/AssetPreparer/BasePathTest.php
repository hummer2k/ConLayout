<?php

namespace ConLayoutTest\AssetPreparer;

use ConLayout\AssetPreparer\BasePath as BasePathPreparer;
use ConLayout\AssetPreparer\BasePathFactory;
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
    protected $basePathPreparer;

    public function setUp()
    {
        $basePathHelper = new BasePath();
        $basePathHelper->setBasePath('/my/base/');

        $this->basePathPreparer = new BasePathPreparer(
            $basePathHelper
        );
    }

    public function testFactory()
    {
        $factory = new BasePathFactory();
        $viewHelperManager = new HelperPluginManager();
        $viewHelperManager->setService('basePath', new BasePath());

        $serviceManager = new ServiceManager();
        $serviceManager->setService('viewHelperManager', $viewHelperManager);

        $instance = $factory->createService($serviceManager);

        $this->assertInstanceOf(
            'ConLayout\AssetPreparer\BasePath',
            $instance
        );

        $this->assertInstanceOf(
            'ConLayout\AssetPreparer\AssetPreparerInterface',
            $instance
        );
    }

    public function testBasePathLocal()
    {
        $value  = '/css/styles.css';
        $result = $this->basePathPreparer->prepare($value);

        $this->assertEquals('/my/base/css/styles.css', $result);
    }

    public function testBasePathRemote()
    {
        $value  = '//cdn.example.com/css/styles.css';
        $result = $this->basePathPreparer->prepare($value);

        $this->assertEquals($value, $result);
    }

    public function testBasePathHttp()
    {
        $value  = 'http://cdn.example.com/css/styles.css';
        $result = $this->basePathPreparer->prepare($value);

        $this->assertEquals($value, $result);
    }

    public function testBasePathHttps()
    {
        $value  = 'https://cdn.example.com/css/styles.css';
        $result = $this->basePathPreparer->prepare($value);

        $this->assertEquals($value, $result);
    }
}