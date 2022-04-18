<?php

namespace Solar\Db\Table;

use Solar\Db\DbConnection;
use Solar\Db\Sql\Insert;
use Solar\Db\Sql\Sql;
use Solar\Db\Table\Row\RowInterface;

class Gateway
{
    const INSERT_DEFAULT    = 0;
    const INSERT_IGNORE     = 1;
    const INSERT_UPDATE     = 2;

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
     * @return DbConnection
     */
    public function getDb(): DbConnection
    {
        return $this->db;
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
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
     * @param array $index
     * @param string|null $rowClass
     * @return array|RowInterface
     * @throws \Exception
     */
    public function fetchRow(array $index, string $rowClass = '')
    {
        $select = $this->sql->select(['*']);

        $select->from($this->schema)->where($index);

        $statement = $select->execute();

        $columns = $statement->fetchAssoc();

        if (empty($rowClass))
            return $columns;

        $row = new $rowClass();

        if (!$row instanceof RowInterface)
            throw new \Exception("Invalid row interface: $rowClass");

        $row->initializeColumns($columns);

        return $row;
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @return array[]
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
     * @param array $where
     * @param array $orderBy
     * @return array
     * @throws \Exception
     */
    public function findOne(array $where, array $orderBy = []): array
    {
        $rows = $this->find($where, $orderBy, [1]);

        return $rows[0] ?? [];
    }

    /**
     * @param int|string|array $indexOrColumns
     * @return bool
     */
    public function hasCompletePrimaryKey($indexOrColumns): bool
    {
        return $this->schema->hasCompletePrimaryKey($indexOrColumns);
    }

    /**
     * @param array $row
     * @param int $type
     * @return array
     * @throws \Exception
     */
    public function insert(array $row, int $type = Insert::TYPE_DEFAULT): array
    {
        try {

            $this->schema->testRow($row);

        } catch (\Exception $exception) {

            throw new \Exception('Unable to insert: ' . $exception->getMessage());
        }

        $insert = $this->sql->insert($type);

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
}