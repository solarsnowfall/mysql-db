<?php

namespace Solar\Db\Sql;

class Limit extends AbstractSqlClause
{
    /**
     * @param array|int $parameters
     */
    public function __construct($parameters)
    {
        $this->parameters = (array) $parameters;

        $this->sqlString = $this->generateSqlString();
    }

    /**
     * @return string
     */
    public function generateSqlString(): string
    {
        return 'LIMIT ' . $this->paramMarker($this->parameters);
    }
}