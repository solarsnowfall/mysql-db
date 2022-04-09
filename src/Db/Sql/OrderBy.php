<?php

namespace Solar\Db\Sql;

class OrderBy extends AbstractSqlClause
{
    public function __construct(array $parameters)
    {
        $this->parameters = $this->parseParameters($parameters);

        $this->sqlString = $this->generateSqlString();
    }

    public function generateSqlString(): string
    {
        if (empty($this->parameters))
            return '';

        return 'ORDER BY ' . implode(', ', $this->parameters);
    }

    protected function parseParameters(array $parameters): array
    {
        $formatted = [];

        foreach ($parameters as $key => $value)
        {
            if (is_string($key))
            {
                $direction = strtoupper($value);

                if ($direction !== 'ASC' && $direction !== 'DESC')
                    throw new \Exception("Invalid ordering direction $value");

                $formatted[] = $this->formatName($key) . " $direction";
            }
            else
            {
                $formatted[] = $this->formatName($value);
            }
        }

        return $formatted;
    }
}