<?php

namespace ConLayout;

use ReflectionMethod;
use ReflectionParameter;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
trait NamedParametersTrait
{
    /**
     *
     * @var array
     */
    protected static $reflectionCache = [];

    /**
     *
     * @param object $instance
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function invokeArgs($instance, $method, array $args = [])
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
