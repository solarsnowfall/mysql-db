<?php

namespace Solar\Object\Property;

use Solar\String\Convention;

abstract class Mapper
{
    const MAGIC_GETTERS = false;

    const MAGIC_SETTERS = false;

    const PROPERTY_CONVENTION = Convention::LOWER_CAMEL_CASE;

    /**
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call(string $name, array $arguments)
    {
        $prefix = substr($name, 0, 3);

        $property = Convention::convert(substr($name, 3), static::PROPERTY_CONVENTION);

        $accessible = (
            $prefix === 'get' && static::MAGIC_GETTERS || $prefix === 'set' && static::MAGIC_SETTERS
        ) && property_exists($this, $property);

        if (!$accessible)
            trigger_error('Call to undefined method ' . get_called_class() . "::$name()");

        if ($prefix === 'get')
            return $this->$property;

        if (count($arguments) === 0)
            throw new \ArgumentCountError('Too few arguments to ' . get_called_class() . "::$name(), 0 passed");

        $this->$property = $arguments[0];

        return $this;
    }

    /**
     * @param int|null $visibility
     * @return array
     */
    public function exportProperties(?int $visibility = Visibility::PARENT_ACCESSIBLE): array
    {
        $properties = [];

        foreach (static::listProperties($visibility) as $property)
            $property[$property] = $this->$property;

        return $properties;
    }

    /**
     * @param array $properties
     * @param int|null $visibility
     * @return Mapper
     */
    public function importProperties(array $properties, ?int $visibility = Visibility::PARENT_ACCESSIBLE): Mapper
    {
        foreach (static::listProperties($visibility) as $property)
            if (isset($properties[$property]))
                $this->$property = $properties[$property];

        return $this;
    }

    /**
     * @param int|null $visibility
     * @return array
     */
    public static function listProperties(?int $visibility = Visibility::PARENT_ACCESSIBLE): array
    {
        $reflection = new \ReflectionClass(get_called_class());

        $propertyList = [];

        foreach ($reflection->getProperties($visibility) as $property)
            if (!$property->isStatic())
                $propertyList[] = $property->name;

        return $propertyList;
    }
}