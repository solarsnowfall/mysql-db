<?php

namespace Solar\Db\Table\Row;

use Solar\Db\DbConnection;
use Solar\Db\Sql\Sql;
use Solar\Db\Table\Gateway as TableGateway;
use Solar\Object\Factory;

class Gateway
{
    /**
     * @var DbConnection
     */
    protected DbConnection $db;

    /**
     * @var TableGateway
     */
    protected TableGateway $gateway;

    /**
     * @var string
     */
    protected string $rowClass;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @param string $table
     * @param string $rowClass
     * @throws \Exception
     */
    public function __construct(string $table, string $rowClass)
    {
        $this->table = $table;

        $this->rowClass = $rowClass;

        $this->db = DbConnection::getInstance();

        $this->gateway = new TableGateway($this->table, $this->rowClass);
    }

    /**
     * @param RowInterface $row
     * @return int
     * @throws \Exception
     */
    public function delete(RowInterface $row): int
    {
        return $this->gateway->delete($row->getIndex());
    }

    /**
     * @param int|string|array $indexOrColumns
     * @param array $columns
     * @return array
     */
    public function extractPrimaryKey($indexOrColumns, array $columns = []): array
    {
        return $this->gateway->getSchema()->extractPrimaryKey($indexOrColumns, $columns);
    }

    /**
     * @param int|string|array $index
     * @return RowInterface
     * @throws \Exception
     */
    public function fetchRow($index): RowInterface
    {
        $index = $this->extractPrimaryKey($index);

        if (!$this->hasCompletePrimaryKey($index))
            throw new \Exception('Attempting to fetch row without a complete primary key');

        $rows = $this->gateway->find($index);

        return $this->newRowInterface($rows[0]);
    }

    /**
     * @param int|string|array $indexOrColumns
     * @return bool
     */
    public function hasCompletePrimaryKey($indexOrColumns): bool
    {
        return $this->gateway->getSchema()->hasCompletePrimaryKey($indexOrColumns);
    }

    /**
     * @param RowInterface $row
     * @return array
     * @throws \Exception
     */
    public function insert(RowInterface $row): array
    {
        $columns = $row->resolveUpdatedColumns();

        $insertId = $this->gateway->insert($columns);

        return $this->extractPrimaryKey($insertId, $columns);
    }

    /**
     * @param RowInterface $row
     * @return int
     * @throws \Exception
     */
    public function update(RowInterface $row)
    {
        $columns = $row->resolveUpdatedColumns();

        if (empty($columns))
            return 0;

        $index = $row->getIndex();

        return $this->gateway->update($columns, $index);
    }

    /**
     * @param array $columns
     * @return RowInterface
     * @throws \Exception
     */
    protected function newRowInterface(array $columns): RowInterface
    {
        $row = Factory::newInstanceOf($this->rowClass, ['columns' => $columns]);

        if (false === $row instanceof RowInterface)
            throw new \Exception("Class $this->rowClass not suitable");

        return $row;
    }
}