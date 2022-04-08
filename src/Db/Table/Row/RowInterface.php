<?php

namespace Solar\Db\Table\Row;

interface RowInterface
{
    /**
     * @return int
     */
    public function delete(): int;

    /**
     * @return array
     */
    public function exportColumns(): array;

    /**
     * @return array
     */
    public function getIndex(): array;

    /**
     * @param array $columns
     * @return RowInterface
     */
    public function initializeColumns(array $columns): RowInterface;

    /**
     * @return array
     */
    public function insert(): array;

    /**
     * @return array
     */
    public function resolveUpdatedColumns(): array;

    /**
     * @return int
     */
    public function update(): int;
}