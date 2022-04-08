<?php

namespace Solar\Object;

class SingletonFactory
{
    /**
     * @var array
     */
    protected static array $instances = [];

    /**
     * @var array
     */
    protected static array $parameters = [];

    /**
     * @param string $class
     * @param array|null $parameters
     * @return mixed
     * @throws \Exception
     */
    public static function getInstanceOf(string $class, array $parameters = null)
    {
        $parameters = $parameters ?? static::$parameters[$class] ?? [];

        if (!isset(static::$instances[$class]))
            static::$instances[$class] = Factory::newInstanceOf($class, $parameters);

        return static::$instances[$class];
    }

    /**
     * @param string $class
     * @param array $parameters
     * @return void
     */
    public static function initializeClass(string $class, array $parameters = [])
    {
        static::$parameters[$class] = $parameters;
    }
}