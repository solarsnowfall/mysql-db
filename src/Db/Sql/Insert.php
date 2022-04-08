<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;
use Solar\Db\Statement;
use Solar\Db\Table\Schema;

class Insert extends AbstractSqlQuery
{
    protected int $rowCount = 0;

    /**
     * @param DbConnection $db
     * @param array $columns
     */
    public function __construct(DbConnection $db, array $columns = [])
    {
        $this->db = $db;

        $this->columns = $columns;
    }

    /**
     * @param $table
     * @return $this
     * @throws \Exception
     */
    public function into($table): Insert
    {
        $this->setTable($table);

        return $this;
    }

    /**
     * @return void
     */
    public function reset()
    {
        parent::reset();

        $this->rowCount = 0;
    }

    /**
     * @param array $row
     * @return Insert
     */
    public function values(array $row): Insert
    {
        foreach ($this->columns as $key => $name)
        {
            $this->preparedColumns[] = [
                'name'  => $name,
                'value' => $row[$name] ?? $row[$key] ?? null
            ];
        }

        $this->rowCount++;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generateSqlString(): string
    {
        if (!count($this->preparedColumns))
            throw new \Exception('No values supplied to insert');

        $table = $this->formatName($this->table->getTable());

        $columnNames = $this->formatNameList($this->columns);

        $sql = "INSERT INTO $table ($columnNames) VALUES ";

        $paramMarkers = '(' . $this->paramMarker($this->columns) . ')';

        $sql .= $paramMarkers . str_repeat(", $paramMarkers", $this->rowCount - 1);

        return $this->formatSqlString($sql);
    }
}