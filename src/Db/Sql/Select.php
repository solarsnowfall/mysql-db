<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;

class Select extends AbstractSqlQuery
{
    public function __construct(DbConnection $db, array $columns = ['*'])
    {
        $this->db = $db;

        $this->columns = $columns;
    }

    /**
     * @return string
     */
    public function generateSqlString(): string
    {
        $columnNames = $this->formatNameList($this->columns);

        $table = $this->formatName($this->table->getTable());

        $sql = "SELECT $columnNames FROM $table";

        if ($this->join !== null)
            $sql .= ' ' . $this->join->getSqlString();

        if ($this->where !== null)
            $sql .= ' ' . $this->where->getSqlString();

        if ($this->orderBy !== null)
            $sql .= ' ' . $this->orderBy->getSqlString();

        if ($this->limit !== null)
            $sql .= ' ' . $this->limit->getSqlString();

        return $sql;
    }
}