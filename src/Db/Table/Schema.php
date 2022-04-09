<?php

namespace Solar\Db\Table;

use Solar\Cache\Memcache;
use Solar\Db\DbConnection;
use Solar\Object\Property\ColumnMapper;

class Schema
{
    const CACHE_EXPIRY = 0;

    /**
     * @var Memcache
     */
    protected Memcache $cache;

    /**
     * @var string
     */
    protected string $cacheKey = '';

    /**
     * @var Column\Schema[]
     */
    protected array $columns = [];

    /**
     * @var DbConnection
     */
    protected DbConnection $db;

    /**
     * @var Column\Schema[][]
     */
    protected array $indexes = [];

    /**
     * @var string
     */
    protected string $schema;

    /**
     * @var string
     */
    protected string $table;

    /**
     * @param string $table
     * @throws \Exception
     */
    public function __construct(string $table)
    {
        $this->table = $table;

        $this->cache = Memcache::getInstance();

        $this->db = DbConnection::getInstance();

        $this->schema = $this->db->getSchema();

        $this->cacheKey = "{$this->schema}.{$this->table}.column_data";

        $this->columns = $this->fetchColumns();

        $this->setIndexes($this->columns);
    }

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    public static function columnExists(string $table, string $column): bool
    {
        try {

            $schema = new Schema($table);

            return $schema->hasColumn($column);

        } catch (\Exception $exception) {

            return false;
        }
    }

    /**
     * @param array $columns
     * @return array
     */
    public function extractIndexColumns(array $columns): array
    {
        $foundColumns = [];

        foreach ($this->indexes as $indexes)
        {
            foreach ($indexes as $index)
            {
                $name = $index->getColumnName();

                if (isset($columns[$name]))
                    $foundColumns[$name] = $columns[$name];
            }
        }

        return $foundColumns;
    }

    /**
     * @param int|string|array $indexOrColumns
     * @param array $columns
     * @return array
     */
    public function extractPrimaryKey($indexOrColumns, array $columns = []): array
    {
        $indexes = $this->getPrimaryKeyColumns();

        $primaryKey = [];

        if (is_scalar($indexOrColumns))
        {
            $primaryKey[$indexes[1]->getColumnName()] = $indexOrColumns;

            if (count($indexes) === 1)
                return $primaryKey;
        }

        for ($i = count($primaryKey) + 1; $i < count($indexes) + 1; $i++)
        {
            $name = $indexes[$i]->getColumnName();

            $primaryKey[$name] = $indexOrColumns[$name] ?? $columns[$name] ?? null;
        }

        return $primaryKey;
    }

    /**
     * @return Column\Schema[]
     * @throws \Exception
     */
    public function fetchColumns(): array
    {
        $rows = $this->cache->get($this->cacheKey, function() {

            $data = static::fetchColumnData();

            if (empty($data))
                throw new \Exception("Table {$this->schema}.{$this->table} not found");

            return $data;

        }, static::CACHE_EXPIRY);

        $columns = [];

        foreach ($rows as $row)
        {
            $row['TABLE_SCHEMA'] = $this->schema;

            $row['TABLE_NAME'] = $this->table;

            $columns[$row['COLUMN_NAME']] = new Column\Schema($row);
        }

        return $columns;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function fetchColumnData(): array
    {
        $sql =  'SELECT ' .
                    '`COLUMNS`.`COLUMN_DEFAULT`, ' .
                    '`COLUMNS`.`COLUMN_KEY`, ' .
                    '`COLUMNS`.`COLUMN_NAME`, ' .
                    '`COLUMNS`.`COLUMN_TYPE`, ' .
                    '`COLUMNS`.`CHARACTER_MAXIMUM_LENGTH`, ' .
                    '`COLUMNS`.`DATA_TYPE`, ' .
                    '`COLUMNS`.`EXTRA`, ' .
                    '`COLUMNS`.`IS_NULLABLE`, ' .
                    '`COLUMNS`.`NUMERIC_PRECISION`, ' .
                    '`COLUMNS`.`NUMERIC_SCALE`, ' .
                    '`COLUMNS`.`TABLE_NAME`, ' .
                    '`COLUMNS`.`TABLE_SCHEMA`, ' .
                    '`STATISTICS`.`INDEX_NAME`, ' .
                    '`STATISTICS`.`SEQ_IN_INDEX`, ' .
                    '`STATISTICS`.`NON_UNIQUE` ' .
                    'FROM `information_schema`.`COLUMNS` ' .
                'LEFT JOIN `information_schema`.`STATISTICS` ON ' .
                '`STATISTICS`.`TABLE_SCHEMA` = `COLUMNS`.`TABLE_SCHEMA` AND ' .
                '`STATISTICS`.`TABLE_NAME` = `COLUMNS`.`TABLE_NAME` AND ' .
                '`STATISTICS`.`COLUMN_NAME` = `COLUMNS`.`COLUMN_NAME` ' .
                'WHERE `COLUMNS`.`TABLE_SCHEMA` LIKE ? ' .
                'AND `COLUMNS`.`TABLE_NAME` LIKE ?';

        return $this->db->fetchAllAssoc($sql, [$this->schema, $this->table], 'ss');
    }

    /**
     * @param string $name
     * @param bool $refresh
     * @return Column\Schema
     * @throws \Exception
     */
    public function getColumn(string $name, bool $refresh = true): Column\Schema
    {
        if (!isset($this->columns[$name]) && !$refresh || !$this->refresh($name))
            throw new \Exception("Column {$this->schema}.{$this->table}.{$name} not found");

        return $this->columns[$name];
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return Column\Schema[][]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param array $columns
     * @return string
     * @throws \Exception
     */
    public function getParamTypes(array $columns)
    {
        $types = '';

        foreach ($columns as $name => $value)
            $types .= $this->getColumn($name)->getParamType($value);

        return $types;
    }

    /**
     * @return Column\Schema[]
     */
    public function getPrimaryKeyColumns(): array
    {
        if (isset($this->indexes['PRIMARY']))
            return $this->indexes['PRIMARY'];

        foreach (array_keys($this->indexes) as $indexName)
            if (!$this->indexes[$indexName][1]->getNonUnique())
                return $this->indexes[$indexName];

        return $this->indexes[key($this->indexes)];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasColumn(string $name): bool
    {
        try {

            return $this->getColumn($name) instanceof Column\Schema;

        } catch (\Exception $exception) {

            return false;
        }
    }

    /**
     * @param int|string|array $indexOrColumns
     * @return bool
     */
    public function hasCompletePrimaryKey($indexOrColumns): bool
    {
        if (is_null($indexOrColumns))
            return false;

        $columns = $this->getPrimaryKeyColumns();

        if (is_scalar($indexOrColumns))
            return count($columns) === 1;

        $found = 0;

        for ($i = 1; $i < count($columns) + 1; $i++)
            if (isset($indexOrColumns[$columns[$i]->getColumnName()]))
                $found++;

        return $found === count($columns);
    }

    /**
     * @param string $checkColumnName
     * @return bool
     * @throws \Exception
     */
    public function refresh(string $checkColumnName)
    {
        $this->cache->delete($this->cacheKey);

        $this->columns = $this->fetchColumns();

        return isset($this->columns[$checkColumnName]);
    }

    /**
     * @return string[]
     */
    public function requiredColumNames(): array
    {
        $required = [];

        foreach ($this->columns as $column)
            if ($column->isRequired())
                $required[] = $column->getColumnName();

        return $required;
    }

    /**
     * @param array $columns
     * @return $this
     * @throws \Exception
     */
    public function testRow(array $columns): Schema
    {
        foreach ($columns as $name => $value)
            if (Column\Schema::VALUE_GOOD !== $result = $this->getColumn($name)->testValue($value))
                throw new \Exception("Invalid value for column $name: $value, $result");

        return $this;
    }

    /**
     * @param Column\Schema[] $columns
     * @return void
     */
    protected function setIndexes(array $columns)
    {
        foreach ($columns as $column)
            if ($column->getIndexName())
                $this->indexes[$column->getIndexName()][$column->getSeqInIndex()] = $column;
    }
}