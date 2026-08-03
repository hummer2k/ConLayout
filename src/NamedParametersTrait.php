<?php

declare(strict_types=1);

namespace ConLayout;

use ReflectionMethod;
use ReflectionParameter;

trait NamedParametersTrait
{
    /**
     *
     * @var array
     */
    protected static array $reflectionCache = [];

    protected function invokeArgs(object $instance, string $method, array $args = []): mixed
    {
        $class = get_class($instance);
        if (isset(self::$reflectionCache[$class][$method])) {
            $reflection = self::$reflectionCache[$class][$method];
        } else {
            $reflection = new ReflectionMethod($instance, $method);
            self::$reflectionCache[$class][$method] = $reflection;
        }

        $pass = [];
        foreach ($reflection->getParameters() as $param) {
            /* @var $param ReflectionParameter */
            if (isset($args[$param->getName()])) {
                $pass[] = $args[$param->getName()];
            } elseif ($param->isDefaultValueAvailable()) {
                $pass[] = $param->getDefaultValue();
            } else {
                $pass[] = current($args);
            }
        }

        return $reflection->invokeArgs($instance, $pass);
    }
}
