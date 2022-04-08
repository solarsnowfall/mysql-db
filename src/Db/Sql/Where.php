<?php

namespace Solar\Db\Sql;

use Solar\Db\Table\Schema;

class Where extends AbstractSqlClause
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;

        $this->sqlString = $this->generateSqlString();
    }

    /**
     * @param bool $compact
     * @return string
     */
    public function generateSqlString(bool $compact = false): string
    {
        return $this->formatSqlString('WHERE ' . $this->parseWhere($this->parameters), $compact);
    }

    /**
     * @return array
     */
    public function getPreparedColumns(): array
    {
        return $this->preparedColumns;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function parseWhere(array $parameters): string
    {
        $sql = '';

        $keys = array_keys($parameters);

        for ($i = 0; $i < count($keys); $i++)
        {
            $key = $keys[$i];

            $value = $parameters[$key];

            $nextKey = $keys[$i + 1] ?? false;

            $nextValue = $parameters[$nextKey] ?? false;

            if (is_int($key))
            {
                if (is_string($value))
                {
                    $expressions = [];

                    $words = explode(' ', $value);

                    for ($j = 0; $j < count($words); $j += 3)
                    {
                        $nameAndFunction = $this->parseName($words[$j]);

                        $name = $this->formatName($words[$j]);

                        $expressions[] = "$name {$words[$j+1]} " . $this->paramMarker($words[$j+2]);

                        $this->preparedColumns[] = [
                            'name'  => $nameAndFunction['name'] ?? $words[$j],
                            'value' => $words[$j+2]
                        ];
                    }

                    $sql .= ' ' . implode(' AND ', $expressions);

                    if ($nextKey !== false && !is_array($nextValue))
                        $sql .= is_string($nextKey) || is_string($nextValue) ? ' AND' : ' OR';
                }
                elseif (is_array($value))
                {
                    $prevKey = $keys[$i - 1] ?? false;

                    if ($prevKey !== false && is_int($prevKey))
                        $sql .= ' OR';

                    $open = count($value) > 1 ? ' (' : '';

                    $close = count($value) > 1 ? ')' : '';

                    $sql .= $open . $this->parseWhere($value) . $close;
                }
            }
            elseif (is_string($key))
            {
                $values = (array) $value;

                $nameAndFunction = $this->parseName($key);

                $name = $this->formatName($key);

                $sql .= " $name";

                if (count($values) > 1)
                    $sql .= ' IN (' . $this->paramMarker($values) . ')';

                if ($nextKey !== false)
                    $sql .= is_string($nextKey) ? ' AND' : ' OR';

                foreach ($values as $v)
                    $this->preparedColumns[] = ['name' => $nameAndFunction['name'] ?? $key, 'value' => $v];
            }
        }

        return $sql;
    }
}