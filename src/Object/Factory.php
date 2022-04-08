<?php

namespace Solar\Object;

class Factory
{
    /**
     * @param string $class
     * @param array $parameters
     *
     * @throws \Exception
     */
    public static function newInstanceOf(string $class, array $parameters = [])
    {
        try {

            $reflection = new \ReflectionClass($class);

        } catch (\ReflectionException $exception) {

            throw new \Exception("Class $class not found");
        }

        $constructor = $reflection->getConstructor();

        try {

            if ($constructor === null)
            {
                $instance = $reflection->newInstance();
            }
            else
            {
                $args = [];

                foreach ($constructor->getParameters() as $parameter)
                {
                    try {

                        $args[] = $parameters[$parameter->name] ?? $parameter->getDefaultValue();

                    } catch (\ReflectionException $exception) {

                        $args[] = null;
                    }
                }

                $instance = $reflection->newInstanceArgs($args);
            }

        } catch (\ReflectionException $exception) {

            throw new \Exception("Class $class not suitable for factory creation");
        }

        return $instance;
    }
}