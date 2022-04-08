<?php

namespace Solar\Db\Sql;

abstract class AbstractSqlClause implements SqlClauseInterface
{
    /**
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var array
     */
    protected array $preparedColumns = [];

    /**
     * @var string|null
     */
    protected ?string $sqlString = null;

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getPreparedColumns(): array
    {
        return $this->preparedColumns;
    }

    /**
     * @param bool $compact
     * @return string
     */
    public function getSqlString(bool $compact = false): string
    {
        if ($this->sqlString === null)
            $this->sqlString = $this->generateSqlString();

        return $this->formatSqlString($this->sqlString, $compact);
    }

    /**
     * @param array $columns
     * @return array
     */
    public function prepareColumns(array $columns): array
    {
        $prepared = [];

        foreach ($columns as $name => $value)
        {
            if (is_int($name))
            {
                array_merge($prepared, $this->prepareColumns($value));
            }
            else
            {
                foreach ((array) $value as $v)
                    $prepared[] = ['name' => $name, 'value' => $v];
            }
        }

        return $prepared;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters): AbstractSqlClause
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param string $name
     * @param bool $param
     * @return string
     */
    public function formatName(string $name, bool $param = false): string
    {
        if ($name === '*')
            return $name;

        $parsed = $this->parseName($name);

        $formatted = '';

        if ($parsed['function'])
            $formatted .= strtoupper($parsed['function']) . '(';

        $words = explode(', ', $parsed['name']);

        foreach ($words as $key => $word)
        {
            $parts = explode('.', $word);

            $words[$key] = '`' . implode('`.`', $parts) . '`';
        }

        $formatted .= implode(', ', $words);

        if ($parsed['function'])
            $formatted .= ')';

        if ($parsed['alias'])
            $formatted .= " AS `{$parsed['alias']}`";

        return $formatted;
    }

    /**
     * @param array $names
     * @param bool $param
     * @param string $separator
     * @return string
     */
    protected function formatNameList(array $names, bool $param = false, string $separator = ', '): string
    {
        foreach ($names as $key => $value)
            $names[$key] = $this->formatName($value, $param);

        return implode($separator, $names);
    }

    /**
     * @param string $sql
     * @param bool $compact
     * @return string
     */
    protected function formatSqlString(string $sql, bool $compact = false): string
    {
        $sql = preg_replace('!\s+!', ' ', $sql);

        if ($compact)
            $sql = preg_replace('/\s*,\s*/', ',', $sql);

        return $sql;
    }

    /**
     * @param string $data
     * @return array
     */
    protected function parseName(string $data): array
    {
        $data = $this->formatSqlString($data, true);

        $result = ['alias' => null, 'function' => null, 'table' => [], 'column' => []];

        $pos1 = strpos($data, '(');

        $pos2 = strpos($data, ')');

        $space = strpos($data, ' ');

        if ($pos1 !== false && $pos1 < $pos2)
        {
            $name = substr($data, $pos1 + 1, $pos2 - ($pos1 + 1));

            $result['function'] = substr($data, 0, $pos1);

            $data = substr($data, $pos2 + 1);
        }
        elseif ($space !== false)
        {
            $name = substr($data, 0, $space);

            $data = substr($data, $space + 1);
        }
        else
        {
            $name = $data;

            $data = '';
        }

        $words = explode(',' , $name);

        foreach ($words as $key => $word)
        {
            $parts = explode('.', str_replace('`', '', $word));

            $result['table'][] = count($parts) > 1 ? $parts[0] : null;

            $result['column'][] = $parts[1] ?? $parts[0];

            $words[$key] = implode('.', $parts);
        }

        $result['name'] = implode(', ', $words);

        if (strlen($data))
        {
            $parts = explode(' ', trim($data));

            $result['alias'] = $parts[1] ?? $parts[0];
        }

        return $result;
    }

    /**
     * @param int|string|array $param
     * @return string
     */
    protected function paramMarker($param): string
    {
        return '?' . str_repeat(', ?', count((array) $param) - 1);
    }
}