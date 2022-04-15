<?php

namespace Solar\Db\Table\Row;

use Solar\Db\Table\TableInterface;

abstract class AbstractRow extends ColumnMapper implements RowInterface
{
    const TABLE = null;

    /**
     * @var array
     */
    private array $initColumns;

    /**
     * @var array
     */
    private array $index;

    /**
     * @var TableInterface
     */
    private TableInterface $table;

    /**
     * @param TableInterface $table
     * @param array $columns
     */
    public function __construct(TableInterface $table, array $columns = [])
    {
        $this->table = $table;

        $this->initializeColumns($columns);
    }

    /**
     * @return array
     */
    public function getIndex(): array
    {
        return $this->index;
    }

    /**
     * @return TableInterface
     */
    protected function getTable(): TableInterface
    {
        return $this->table;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function delete(): int
    {
        $affectedRows = $this->table->deleteRow($this);

        if ($affectedRows)
        {
            foreach (static::listColumns() as $property)
                unset($this->$property);

            $this->setInitColumns([]);

            $this->index = [];
        }

        return $affectedRows;
    }

    /**
     * @return RowInterface
     * @throws \Exception
     */
    public function fetch(): RowInterface
    {
        if (!$this->table->hasCompletePrimaryKey($this->index))
            throw new \Exception('Attempting to fetch row in incomplete key');

        $rows = $this->table->getGateway()->find($this->index);

        return $this->initializeColumns($rows[0]);
    }

    /**
     * @param array $columns
     * @return AbstractRow
     */
    public function initializeColumns(array $columns): AbstractRow
    {
        $this->index = $this->table->extractPrimaryKey($columns);

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
        return $this->table->insertRow($this);
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
        if ($this->table->hasCompletePrimaryKey($this->index))
            return $this->update();

        return $this->insert();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function update(): int
    {
        return $this->table->updateRow($this);
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