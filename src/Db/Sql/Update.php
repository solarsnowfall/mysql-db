<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;
use Solar\Db\Table\Schema;

class Update extends AbstractSqlQuery
{
    /**
     * @param DbConnection $db
     * @param Schema|string $table
     * @throws \Exception
     */
    public function __construct(DbConnection $db, $table = null)
    {
        $this->db = $db;

        if ($table !== null)
            $this->setTable($table);
    }

    /**
     * @return string
     */
    public function generateSqlString(): string
    {
        $table = $this->formatName($this->table->getTable());

        $columnsEqual = $this->formatNameList($this->columns, true);

        $where = $this->where->getSqlString();

        $sql = "UPDATE $table SET $columnsEqual $where";

        return $this->formatSqlString($sql);
    }

    /**
     * @param array $values
     * @return $this
     */
    public function set(array $values): Update
    {
        $this->columns = array_keys($values);

        $this->preparedColumns = $this->prepareColumns($values);

        return $this;
    }

    /**
     * @param Schema|string $table
     * @return $this
     * @throws \Exception
     */
    public function table($table): Update
    {
        $this->setTable($table);

        return $this;
    }
}