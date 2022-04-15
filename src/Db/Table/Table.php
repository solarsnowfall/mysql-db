<?php

namespace Solar\Db\Table;

use Solar\Db\Table\Row\RowInterface;

class Table implements TableInterface
{
    /**
     * @var Gateway
     */
    protected Gateway $gateway;

    /**
     * @var string
     */
    protected string $rowPrototype;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @throws \Exception
     */
    public function __construct(string $table, string $rowPrototype)
    {
        $this->table = $table;

        $this->rowPrototype = $rowPrototype;

        if (!is_subclass_of($this->rowPrototype, RowInterface::class))
            throw new \Exception('Row prototype must implement RowInterface');

        $this->gateway = new Gateway($this->table);
    }

    /**
     * @return Gateway
     */
    public function getGateway(): Gateway
    {
        return $this->gateway;
    }

    /**
     * @param RowInterface $row
     * @return int
     * @throws \Exception
     */
    public function deleteRow(RowInterface $row): int
    {
        return $this->gateway->delete($row->getIndex());
    }

    /**
     * @param int|string|array $indexOrColumns
     * @return array
     */
    public function extractPrimaryKey($indexOrColumns): array
    {
        return $this->getGateway()->extractPrimaryKey($indexOrColumns);
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @return RowInterface[]
     * @throws \Exception
     */
    public function fetchAll(array $where, array $orderBy = [], array $limit = []): array
    {
        $rows = [];

        foreach ($this->gateway->find($where, $orderBy, $limit) as $columns)
            $rows[] = new $this->rowPrototype($columns);

        return $rows;
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @return RowInterface
     * @throws \Exception
     */
    public function fetchRow(array $where, array $orderBy = []): RowInterface
    {
        $rows = $this->gateway->find($where, $orderBy, [1]);

        return new $this->rowPrototype($rows[0]);
    }

    /**
     * @param int|string|array $indexOrColumns
     * @return bool
     */
    public function hasCompletePrimaryKey($indexOrColumns): bool
    {
        return $this->gateway->hasCompletePrimaryKey($indexOrColumns);
    }

    /**
     * @param RowInterface $row
     * @return array
     * @throws \Exception
     */
    public function insertRow(RowInterface $row): array
    {
        return $this->gateway->insert($row->exportColumns());
    }

    /**
     * @param RowInterface $row
     * @return int
     * @throws \Exception
     */
    public function updateRow(RowInterface $row): int
    {
        return $this->gateway->update($row->getIndex(), $row->resolveUpdatedColumns());
    }
}