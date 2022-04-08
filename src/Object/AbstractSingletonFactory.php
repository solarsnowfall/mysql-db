<?php

namespace Solar\Object;

abstract class AbstractSingletonFactory
{
    /**
     * @param array|null $parameters

     * @throws \Exception
     */
    public static function getInstance(array $parameters = null)
    {
        $instance = SingletonFactory::getInstanceOf(get_called_class(), $parameters);

        if ($instance === null)
            throw new \Exception('Unable to get instance of ' . get_called_class());

        return $instance;
    }

    /**
     * @param array $parameters
     * @return void
     */
    public static function initialize(array $parameters = [])
    {
        SingletonFactory::initializeClass(get_called_class(), $parameters);
    }
}