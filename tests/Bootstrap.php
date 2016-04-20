<?php
// @codingStandardsIgnoreFile
namespace ConLayoutTest;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

error_reporting(E_ALL);

class Bootstrap
{
    protected static $serviceManager;
    protected static $bootstrap;

    public static function init()
    {
        static::initAutoloader();
        static::initServiceManager();
    }

    protected static function initServiceManager()
    {
        $baseConfig = array(
            'modules' => [
                'ConLayout'
            ],
            'module_listener_options' => array(
                'module_paths' => [
                    dirname(__DIR__)
                ],
            ),
        );
        $config = $baseConfig;

        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);
        $smConfig = new ServiceManagerConfig();
        $smConfig->configureServiceManager($serviceManager);

        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        static::$serviceManager = $serviceManager;
    }

    /**
     *
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = __DIR__ . '/../vendor';
        if (is_readable($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }
    }
}

Bootstrap::init();