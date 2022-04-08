<?php

namespace Solar\Object;

abstract class AbstractFactory
{
    /**
     * @param array $parameters
     * @return object|null
     * @throws \Exception
     */
    public static function newInstance(array $parameters = [])
    {
        return Factory::newInstanceOf(get_called_class(), $parameters);
    }
}