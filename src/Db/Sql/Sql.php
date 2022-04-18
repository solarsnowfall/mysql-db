<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;
use Solar\Db\Table\Schema;

class Sql
{
    protected DbConnection $db;

    public function __construct(DbConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @return Delete
     */
    public function delete(): Delete
    {
        return new Delete($this->db);
    }

    /**
     * @param int $type
     * @return Insert
     */
    public function insert(int $type = Insert::TYPE_DEFAULT): Insert
    {
        return new Insert($this->db, $type);
    }

    /**
     * @param array $columns
     * @return Select
     */
    public function select(array $columns = ['*'])
    {
        return new Select($this->db, $columns);
    }

    /**
     * @param Schema|string|null $table
     * @return Update
     * @throws \Exception
     */
    public function update($table = null)
    {
        return new Update($this->db, $table);
    }
}