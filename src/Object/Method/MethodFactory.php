<?php

namespace Solar\Object\Method;

class MethodFactory
{
    /**
     * @param mixed $object
     * @param string $method
     * @param array $params
     * @param array $required
     * @return mixed
     * @throws \Exception
     */
    public static function invoke($object, string $method, array $params = [], array $required = [])
    {
        $missing = [];

        foreach ($required as $name)
            if (!isset($required[$name]))
                $missing[] = $name;

        if (count($missing))
            throw new \Exception('Missing required parameters: ' . implode(', ', $missing));

        if (strpos($method, '::') === false)
            $method = get_class($object) . '::' . $method;

        try {

            $reflection = new \ReflectionMethod($method);

            $args = [];

            foreach ($reflection->getParameters() as $parameter)
            {
                try {

                    $args[] = $params[$parameter->name] ?? $parameter->getDefaultValue();

                } catch (\ReflectionException $exception) {

                    $args[] = null;
                }
            }

            return $reflection->invokeArgs($object, $args);

        } catch (\ReflectionException $exception) {

            throw new \Exception("Method {$method} not suitable for factory invocation");
        }
    }
}