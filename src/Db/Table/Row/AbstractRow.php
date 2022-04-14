<?php

namespace Solar\Db\Table\Row;

use Solar\Db\Table\Gateway;

abstract class AbstractRow extends ColumnMapper implements RowInterface
{
    const TABLE = null;

    /**
     * @var Gateway
     */
    private Gateway $gateway;

    /**
     * @var array
     */
    private array $initColumns;

    /**
     * @var array
     */
    private array $index;

    /**
     * @param array $columns
     * @throws \Exception
     */
    public function __construct(array $columns = [])
    {
        $this->gateway = new Gateway(static::TABLE);

        $this->initializeColumns($columns);
    }

    /**
     * @return Gateway
     */
    protected function getGateway(): Gateway
    {
        return $this->gateway;
    }

    /**
     * @return array
     */
    public function getIndex(): array
    {
        return $this->index;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function delete(): int
    {
        $affectedRows = $this->gateway->delete($this->getIndex());

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
        if (!$this->gateway->hasCompletePrimaryKey($this->getIndex()))
            throw new \Exception('Attempting to fetch row in incomplete key');

        $columns = $this->gateway->fetchRow($this->getIndex());

        return $this->initializeColumns($columns);
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
        return $this->gateway->insert($this->resolveUpdatedColumns());
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
        return $this->gateway->update($this->getIndex(), $this->resolveUpdatedColumns());
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