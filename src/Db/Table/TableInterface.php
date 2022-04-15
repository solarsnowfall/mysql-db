<?php

namespace Solar\Db\Table;

use Solar\Db\Table\Row\RowInterface;

interface TableInterface
{
    /**
     * @param RowInterface $row
     * @return int
     */
    public function deleteRow(RowInterface $row): int;

    /**
     * @param int|string|array $indexOrColumns
     * @return array
     */
    public function extractPrimaryKey($indexOrColumns): array;

    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @return RowInterface[]
     */
    public function fetchAll(array $where, array $orderBy = [], array $limit = []): array;

    /**
     * @param array $where
     * @param array $orderBy
     * @return RowInterface
     */
    public function fetchRow(array $where, array $orderBy = []): RowInterface;

    /**
     * @return Gateway
     */
    public function getGateway(): Gateway;

    /**
     * @param int|string|array $indexOrColumns
     * @return bool
     */
    public function hasCompletePrimaryKey($indexOrColumns): bool;

    /**
     * @param RowInterface $row
     * @return array
     */
    public function insertRow(RowInterface $row): array;

    /**
     * @param RowInterface $row
     * @return int
     */
    public function updateRow(RowInterface $row): int;
}