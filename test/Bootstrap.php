<?php

declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace ConLayoutTest;

use ConLayout\ConfigProvider;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\LaminasView\ConfigProvider as LaminasViewConfigProvider;

error_reporting(E_ALL);

class Bootstrap
{
    protected static ?ServiceManager $serviceManager = null;

    public static function init(): void
    {
        static::initAutoloader();
        static::initServiceManager();
    }

    protected static function initServiceManager(): void
    {
        $config = [];

        foreach ([LaminasViewConfigProvider::class, ConfigProvider::class] as $providerClass) {
            $provider = new $providerClass();
            $config = static::merge($config, $provider());
        }

        $testConfig = include __DIR__ . '/TestConfig.php.dist';
        $config = static::merge($config, $testConfig);

        $serviceManager = new ServiceManager($config['dependencies']);
        $serviceManager->setService('config', $config);

        static::$serviceManager = $serviceManager;
    }

    public static function getServiceManager(): ServiceManager
    {
        return static::$serviceManager;
    }

    protected static function merge(array $target, array $source): array
    {
        foreach ($source as $key => $value) {
            if (isset($target[$key]) && is_array($target[$key]) && is_array($value)) {
                $target[$key] = static::merge($target[$key], $value);
            } else {
                $target[$key] = $value;
            }
        }
        return $target;
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
