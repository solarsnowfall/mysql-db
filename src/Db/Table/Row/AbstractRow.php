<?php

namespace Solar\Db\Table\Row;

abstract class AbstractRow extends ColumnMapper implements RowInterface
{
    const TABLE = null;

    private Gateway $gateway;

    private array $initColumns;

    private array $index;

    /**
     * @param array $columns
     * @param string $table
     * @throws \Exception
     */
    public function __construct(array $columns = [])
    {
        $this->gateway = new Gateway(static::TABLE, static::class);

        $this->initializeColumns($columns);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function delete(): int
    {
        $affectedRows = $this->gateway->delete($this);

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
     * @return array
     */
    public function getIndex(): array
    {
        return $this->index;
    }

    /**
     * @param array $columns
     * @return AbstractRow
     */
    public function initializeColumns(array $columns): AbstractRow
    {
        $this->index = $this->gateway->extractPrimaryKey($columns);

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
        return $this->gateway->insert($this);
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
        if ($this->gateway->hasCompletePrimaryKey($this->getIndex()))
            return $this->update();

        return $this->insert();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function update(): int
    {
        return $this->gateway->update($this);
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