<?php

declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace ConLayoutTest;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

error_reporting(E_ALL);

class Bootstrap
{
    protected static ?ServiceManager $serviceManager = null;
    protected static mixed $bootstrap = null;

    public static function init(): void
    {
        static::initAutoloader();
        static::initServiceManager();
    }

    protected static function initServiceManager(): void
    {
        $config = include __DIR__ . '/TestConfig.php.dist';

        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);
        $smConfig = new ServiceManagerConfig();
        $smConfig->configureServiceManager($serviceManager);

        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        static::$serviceManager = $serviceManager;
    }

    public static function getServiceManager(): ServiceManager
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader(): void
    {
        $vendorPath = __DIR__ . '/../vendor';
        if (is_readable($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }
    }
}

Bootstrap::init();
