<?php

namespace Solar\Db\Table\Row;

use Solar\Db\Table\Gateway;

abstract class AbstractRow extends ColumnMapper implements RowInterface
{
    const TABLE = null;

    /**
     * @var array
     */
    private array $initColumns = [];

    /**
     * @var array
     */
    private array $index = [];

    /**
     * @var Gateway
     */
    private Gateway $tableGateway;

    /**
     * @param int|string|array|null $indexOrColumns
     * @throws \Exception
     */
    public function __construct($indexOrColumns = null)
    {
        $this->tableGateway = new Gateway(static::TABLE);

        if ($indexOrColumns !== null)
            $this->initializeColumns($indexOrColumns);
    }

    /**
     * @return array
     */
    public function getIndex(): array
    {
        return $this->index;
    }

    /**
     * @return Gateway
     */
    protected function getTableGateway(): Gateway
    {
        return $this->tableGateway;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function delete(): int
    {
        if ($this->tableGateway->hasCompletePrimaryKey($this->index))
            return 0;

        $affectedRows = $this->tableGateway->delete($this->index);

        if ($affectedRows)
        {
            foreach (static::listColumns() as $property)
                unset($this->$property);

            $this->initializeColumns([]);
        }

        return $affectedRows;
    }

    /**
     * @param int|string|array $indexOrColumns
     * @return $this
     * @throws \Exception
     */
    public function initializeColumns($indexOrColumns): AbstractRow
    {
        $this->index = $this->tableGateway->extractPrimaryKey($indexOrColumns);

        $columns = is_array($indexOrColumns) ? $indexOrColumns : $this->index;

        if (count($this->index) === count($columns) && $this->tableGateway->hasCompletePrimaryKey($this->index))
            $columns = $this->tableGateway->findOne($this->index);

        $this->setInitColumns($columns);

        $this->importColumns($columns);

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function insert(): array
    {
        return $this->tableGateway->insert($this->resolveUpdatedColumns());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function populate(): array
    {
        if (!$this->tableGateway->hasCompletePrimaryKey($this->index))
            throw new \Exception('Cannot populate values without a complete kry');

        $columns = $this->tableGateway->findOne($this->index);

        $this->setInitColumns($columns);

        $this->importColumns($columns);

        return $columns;
    }

    /**
     * @return array
     */
    public function resolveUpdatedColumns(): array
    {
        $columns = [];

        foreach (static::listColumns() as $name)
            if ($this->initColumns[$name] !== $this->$name)
                $columns[$name] = $this->$name;

        return $columns;
    }

    /**
     * @return array|int
     * @throws \Exception
     */
    public function save()
    {
        if ($this->tableGateway->hasCompletePrimaryKey($this->index))
            return $this->update();

        return $this->insert();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function update(): int
    {
        $columns = $this->resolveUpdatedColumns();

        if (empty($columns))
            return 0;

        return $this->tableGateway->update($this->index, $columns);
    }

    /**
     * @param array $columns
     * @return AbstractRow
     */
    final private function setInitColumns(array $columns): AbstractRow
    {
        foreach (static::listColumns() as $name)
            $this->initColumns[$name] = $columns[$name] ?? null;

        return $this;
    }
}