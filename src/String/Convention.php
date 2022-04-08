<?php

namespace Solar\String;

class Convention
{
    const CAMEL_CASE = 0;

    const LOWER_CAMEL_CASE = 1;

    const LOWER_HYPHENATED = 2;

    const LOWER_UNDERSCORE = 3;

    const UPPER_HYPHENATED = 4;

    const UPPER_UNDERSCORE = 5;

    /**
     * @param string $data
     * @param int $convention
     * @return string
     */
    public static function convert(string $data, int $convention)
    {
        switch ($convention)
        {
            case self::CAMEL_CASE:

                return self::toCamelCase($data);

            case self::LOWER_CAMEL_CASE:

                return self::toLowerCamelCase($data);

            case self::LOWER_HYPHENATED:

                return self::toLowerHyphenated($data);

            case self::LOWER_UNDERSCORE:

                return self::toLowerUnderscore($data);

            case self::UPPER_HYPHENATED:

                return self::toUpperHyphenated($data);

            case self::UPPER_UNDERSCORE:

                return self::toUpperUnderscore($data);
        }

        return $data;
    }

    /**
     * @param string[] $data
     * @param int $convention
     * @param bool $deep
     * @return array
     */
    public static function convertArray(array $data, int $convention, bool $deep = false): array
    {
        foreach ($data as $key => $value)
        {
            $data[$key] = $deep && is_array($value)
                ? self::convertArray($value, $convention, true)
                : self::convert($value, $convention);
        }

        return $data;
    }


    /**
     * @param array $data
     * @param int $convention
     * @param bool $deep
     * @return array
     */
    public static function convertKeys(array $data, int $convention, bool $deep = false): array
    {
        $output = [];

        foreach (array_keys($data) as $key)
        {
            $newKey = self::convert($key, $convention);

            $output[$newKey] = $deep && is_array($data[$key])
                ? self::convertKeys($data[$key], $convention, true)
                : $data[$key];
        }

        return $output;
    }

    /**
     * @param string $data
     * @return string
     */
    public static function toCamelCase(string $data): string
    {
        if (empty($data))
            return $data;

        $data = self::toLowerUnderscore($data);

        $data = ucwords($data, '_');

        return str_replace('_', '', $data);
    }

    /**
     * @param string $data
     * @return string
     */
    public static function toLowerCamelCase(string $data): string
    {
        if (empty($data))
            return $data;

        $data = self::toCamelCase($data);

        return lcfirst($data);
    }

    /**
     * @param string $data
     * @return string
     */
    public static function toLowerHyphenated(string $data): string
    {
        if (empty($data))
            return $data;

        $data = self::toLowerUnderscore($data);

        return str_replace('_', '-', $data);
    }

    /**
     * @param string $data
     * @return string
     */
    public static function toLowerUnderscore(string $data): string
    {
        if (empty($data))
            return $data;

        $data = str_replace('-', '_', $data);

        if (strpos($data, '_') !== false || strtoupper($data) === $data)
            return strtolower($data);

        $data = preg_replace('/(?<!^)[A-Z]/', '_$0', $data);

        return strtolower($data);
    }

    /**
     * @param string $data
     * @return string
     */
    public static function toUpperHyphenated(string $data): string
    {
        if (empty($data))
            return $data;

        $data = self::toLowerHyphenated($data);

        return strtoupper($data);
    }

    /**
     * @param string $data
     * @return string
     */
    public static function toUpperUnderscore(string $data): string
    {
        if (empty($data))
            return $data;

        $data = self::toLowerUnderscore($data);

        return strtoupper($data);
    }
}