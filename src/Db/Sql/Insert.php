<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;

class Insert extends AbstractSqlQuery
{
    const TYPE_DEFAULT  = 0;
    const TYPE_IGNORE   = 1;
    const TYPE_UPDATE   = 2;

    /**
     * @var int
     */
    protected int $rowCount = 0;

    /**
     * @var int
     */
    protected int $type;

    /**
     * @param DbConnection $db
     * @param int $type
     * @param array $columns
     */
    public function __construct(DbConnection $db, int $type = self::TYPE_DEFAULT, array $columns = [])
    {
        $this->db = $db;

        $this->type = $type;

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

        $ignore = $this->type == self::TYPE_IGNORE ? 'IGNORE' : '';

        $sql = "INSERT $ignore INTO $table ($columnNames) VALUES ";

        $paramMarkers = '(' . $this->paramMarker($this->columns) . ')';

        $sql .= $paramMarkers . str_repeat(", $paramMarkers", $this->rowCount - 1);

        if ($this->type == self::TYPE_UPDATE)
        {
            $sql .= " ON DUPLICATE KEY UPDATE ";

            foreach ($this->columns as $column)
                $sql .= "`$column` = VALUES(`$column`), ";

            $sql = substr($sql, 0, -2);
        }

        return $this->formatSqlString($sql);
    }
}