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
     * @param Schema|string $table
     * @return AbstractSqlClause
     * @throws \Exception
     */
    public function setTable($table): AbstractSqlClause
    {
        if (!$table instanceof Schema)
            $table = new Schema($table);

        $this->table = $table;

        return $this;
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
                        $parsed = $this->parseName($words[$j]);

                        $name = $this->formatName($words[$j]);

                        $isColumn = !$this->isNotColumn($words[$j+1]);

                        $param = !$isColumn ? $this->paramMarker($words[$j+2]) : $words[$j+2];

                        $expressions[] = "$name {$words[$j+1]} $param";

                        if (!$isColumn)
                        {
                            $this->preparedColumns[] = [
                                'name'  => $parsed['name'] ?? $words[$j],
                                'value' => $words[$j+2]
                            ];
                        }
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

                $parsed = $this->parseName($key);

                $name = $this->formatName($key, count($values) === 1);

                $sql .= " $name";

                if (count($values) > 1)
                    $sql .= ' IN (' . $this->paramMarker($values) . ')';

                if ($nextKey !== false)
                    $sql .= is_string($nextKey) ? ' AND' : ' OR';

                foreach ($values as $v)
                    $this->preparedColumns[] = ['name' => $parsed['name'] ?? $key, 'value' => $v];
            }
        }

        return $sql;
    }
}