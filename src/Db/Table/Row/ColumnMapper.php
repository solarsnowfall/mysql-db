<?php

namespace Solar\Db\Table\Row;

use Solar\Object\Property\Mapper;
use Solar\Object\Property\Visibility;
use Solar\String\Convention;

class ColumnMapper extends Mapper
{
    const COLUMN_ALIAS          = [];
    const COLUMN_CONVENTION     = null;
    const COLUMN_VISIBILITY     = Visibility::PROTECTED;
    const PROPERTY_CONVENTION   = parent::PROPERTY_CONVENTION;

    /**
     * @return array
     */
    public function exportColumns(): array
    {
        $properties = $this->exportProperties(static::COLUMN_VISIBILITY);

        foreach (static::COLUMN_ALIAS as $name => $alias)
        {
            $properties[$name] = $properties[$alias];

            unset($properties[$alias]);
        }

        return Convention::convertKeys($properties, static::COLUMN_CONVENTION);
    }

    /**
     * @param array $columns
     * @return ColumnMapper
     */
    public function importColumns(array $columns): self
    {
        foreach (static::COLUMN_ALIAS as $name => $alias)
        {
            if (isset($columns[$name]))
            {
                $columns[$alias] = $columns[$name];

                unset($columns[$name]);
            }
        }

        $properties = Convention::convertKeys($columns, static::PROPERTY_CONVENTION);

        return $this->importProperties($properties, static::COLUMN_VISIBILITY);
    }

    /**
     * @return array
     */
    public static function listColumns(): array
    {
        $properties = static::listProperties(static::COLUMN_VISIBILITY);

        foreach (self::COLUMN_ALIAS as $name => $alias)
        {
            $properties[$name] = $properties[$alias];

            unset($properties[$alias]);
        }

        return Convention::convertKeys($properties, static::COLUMN_CONVENTION);
    }


}