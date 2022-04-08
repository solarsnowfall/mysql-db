<?php

namespace Solar\Db\Sql;

use Solar\Db\Table\Schema;

class Join extends AbstractSqlClause
{
    const CROSS = 'CROSS';

    const INNER = 'INNER';

    const LEFT = 'LEFT';

    const NATURAL = 'NATURAL';

    const OUTER = 'OUTER';

    const RIGHT = 'RIGHT';

    const STRAIGHT_JOIN = 'STRAIGHT_JOIN';

    protected string $joinType = '';

    /**
     * @param $joinType
     * @return string
     */
    protected function parseJoinType($joinType): string
    {
        if ($joinType === null)
            return '';

        if (is_string($joinType))
            $joinType = explode(' ', $joinType);

        $lookup = array_flip($joinType);

        if (isset($lookup[self::STRAIGHT_JOIN]))
            return self::STRAIGHT_JOIN;

        if (isset($lookup[self::INNER]) || isset($lookup[self::CROSS]))
            return isset($lookup[self::INNER]) ? self::INNER : self::CROSS;

        $sql = isset($lookup[self::NATURAL]) ? self::NATURAL : '';

        if (isset($lookup[self::LEFT]) || isset($lookup[self::RIGHT]))
        {
            if (strlen($sql))
                $sql .= ' ';

            $sql .= isset($lookup[self::LEFT]) ? self::LEFT : self::RIGHT;

            if (isset($lookup[self::OUTER]))
                $sql .= ' ' . self::OUTER;
        }

        return $sql;
    }

    /**
     * @return string
     */
    public function generateSqlString(): string
    {
        $sql = '';

        foreach ($this->parameters as $join)
        {
            $sql .= $join['type'];

            $sql .= strlen($sql) ? ' JOIN ' : 'JOIN ';

            $sql .= $this->formatName($join['table']->getTable()) . ' ON ';

            $sql .= $this->parseOn($join['on']);
        }

        return $sql;
    }

    /**
     * @param Schema|string $table
     * @param array|string $on
     * @param array|string $type
     * @return void
     * @throws \Exception
     */
    public function join($table, $on, $type = null)
    {
        $this->parameters[] = [
            'table'     => (!$table instanceof Schema) ? new Schema($table) : $table,
            'on'        => $on,
            'type'      => $this->parseJoinType($type)
        ];
    }

    /**
     * @param array $on
     * @return string
     */
    protected function parseOn($on)
    {
        if (is_string($on))
            $on = [$on];

        $sql = '';

        foreach ($on as $key => $value)
        {
            if (is_int($key))
            {
                if (is_string($value))
                {
                    $words = explode(' ', $value);

                    $sql .= $this->formatName($words[0]) . " {$words[1]} " . $this->formatName($words[2]);
                }
                elseif (is_array($value))
                {
                    $sql .= ' ' . $this->parseJoinType($value);
                }
            }
            else
            {
                $sql .= ' ' . $this->formatName($key) . ' = ' . $this->formatName($value);
            }
        }

        return $this->formatSqlString($sql);
    }
}