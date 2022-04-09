<?php

namespace Solar\Db\Table;

use Solar\Db\DbConnection;
use Solar\Db\Sql\Sql;

class Gateway
{
    const INSERT_DEFAULT = 0;

    const INSERT_IGNORE = 1;

    const INSERT_UPDATE = 2;

    /**
     * @var DbConnection
     */
    protected DbConnection $db;

    /**
     * @var Schema
     */
    protected Schema $schema;

    /**
     * @var Sql
     */
    protected Sql $sql;

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

        $this->db = DbConnection::getInstance();

        $this->schema = new Schema($table);

        $this->sql = new Sql($this->db);
    }

    /**
     * @param array $index
     * @return int
     * @throws \Exception
     */
    public function delete(array $index): int
    {
        $delete = $this->sql->delete();

        return $delete->where($index)->execute()->affectedRows();
    }

    /**
     * @param int|string|array $indexOrColumns
     * @param array $columns
     * @return array
     */
    public function extractPrimaryKey($indexOrColumns, array $columns = []): array
    {
        return $this->schema->extractPrimaryKey($indexOrColumns, $columns);
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @return array
     * @throws \Exception
     */
    public function find(array $where, array $orderBy = [], array $limit = []): array
    {
        $select = $this->sql->select(['*']);

        $select->from($this->schema)->where($where)->orderBy($orderBy)->limit($limit);

        $statement = $select->execute();

        return $statement->fetchAllAssoc();
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return substr($this->table, 1, -1);
    }

    /**
     * @param array $row
     * @return array
     * @throws \Exception
     */
    public function insert(array $row): array
    {
        try {

            $this->schema->testRow($row);

        } catch (\Exception $exception) {

            throw new \Exception('Unable to insert: ' . $exception->getMessage());
        }

        $insert = $this->sql->insert();

        $statement = $insert->into($this->schema)->columns($row)->values($row)->execute();

        $insertId = $statement->insertId();

        return $this->extractPrimaryKey($insertId, $row);
    }

    /**
     * @param $index
     * @param array $columns
     * @return int
     * @throws \Exception
     */
    public function update($index, array $columns)
    {
        $index = $this->schema->extractPrimaryKey($index);

        if (!$this->schema->hasCompletePrimaryKey($index))
            throw new \Exception('Cannot update row on incomplete key');

        $this->schema->testRow($columns);

        $update = $this->sql->update($this->schema);

        $statement = $update->set($columns)->where($index)->execute();

        return $statement->affectedRows();
    }

    /**
     * @param string $name
     * @param bool $param
     * @return string
     */
    protected function formatName(string $name, bool $param = false): string
    {
        if ($name === '*')
            return $name;

        $parts = explode('.', $name);

        $suffix = $param ? " = ?" : '';

        return '`' . implode('`.`', $parts) . '`' . $suffix;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }
}