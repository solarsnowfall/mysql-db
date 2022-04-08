<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;
use Solar\Db\Table\Schema;

class Delete extends AbstractSqlQuery
{
    /**
     * @param DbConnection $db
     */
    public function __construct(DbConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @param bool $compact
     * @return string
     */
    public function generateSqlString(bool $compact = false): string
    {
        $table = $this->formatName($this->table->getTable());

        $where = $this->where->generateSqlString();

        $sql = "DELETE FROM $table $where";

        if ($this->orderBy !== null)
            $sql .= ' ' . $this->orderBy->getSqlString();

        if ($this->limit !== null)
            $sql .= ' ' . $this->limit->getSqlString();

        return $this->formatSqlString($sql);
    }
}