<?php

namespace Solar\Db\Sql;

use Solar\Db\DbConnection;
use Solar\Db\Statement;
use Solar\Db\Table\Schema;

abstract class AbstractSqlQuery extends AbstractSqlClause implements SqlQueryInterface
{
    /**
     * @var array
     */
    protected array $columns = [];

    /**
     * @var DbConnection
     */
    protected DbConnection $db;

    /**
     * @var Join|null
     */
    protected ?Join $join = null;

    /**
     * @var Limit|null
     */
    protected ?Limit $limit = null;

    /**
     * @var OrderBy|null
     */
    protected ?OrderBy $orderBy = null;

    /**
     * @var Schema|null
     */
    protected ?Schema $table = null;

    /**
     * @var Where|null
     */
    protected ?Where $where = null;

    /**
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns): SqlQueryInterface
    {
        $this->columns = is_string(key($columns)) ? array_keys($columns) : $columns;

        return $this;
    }

    /**
     * @return Statement
     * @throws \Exception
     */
    public function execute(): Statement
    {
        $sql = $this->generateSqlString();

        $params = []; $types = '';

        if ($this->where !== null)
        {
            foreach ($this->where->getPreparedColumns() as $column)
            {
                $params[] = $column['value'];

                $parts = explode('.', $column['name']);

                $table = count($parts) === 2 ? $parts[0] : null;

                $name = count($parts) === 2 ? $parts[1] : $parts[0];

                $table = $table === null ? $this->table : new Schema($table);

                $types .= $table->getColumn($name)->getParamType($column['value']);
            }
        }

        if ($this->limit !== null)
        {
            foreach ($this->limit->getParameters() as $parameter)
            {
                $params[] = $parameter;

                $types .= 'i';
            }
        }

        return $this->db->execute($sql, $params, $types);
    }

    /**
     * @param $table
     * @return SqlQueryInterface
     * @throws \Exception
     */
    public function from($table): SqlQueryInterface
    {
        $this->setTable($table);

        return $this;
    }

    /**
     * @param $table
     * @return SqlQueryInterface
     * @throws \Exception
     */
    public function into($table): SqlQueryInterface
    {
        $this->setTable($table);

        return $this;
    }

    /**
     * @param $table
     * @param $on
     * @param $type
     * @return SqlQueryInterface
     * @throws \Exception
     */
    public function join($table, $on, $type = null): SqlQueryInterface
    {
        if ($this->join === null)
            $this->join = new Join();

        $this->join->join($table, $on, $type);

        return $this;
    }

    /**
     * @param int|array $limit
     * @return SqlQueryInterface
     */
    public function limit($limit): SqlQueryInterface
    {
        $this->limit = new Limit($limit);

        return $this;
    }

    /**
     * @param array $orderBy
     * @return SqlQueryInterface
     */
    public function orderBy(array $orderBy): SqlQueryInterface
    {
        $this->orderBy = new OrderBy($orderBy);

        return $this;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->columns = [];

        $this->limit = null;

        $this->orderBy = null;

        $this->preparedColumns = [];

        $this->table = null;

        $this->where = null;
    }

    /**
     * @param array $where
     * @return SqlQueryInterface
     */
    public function where(array $where): SqlQueryInterface
    {
        $this->where = new Where($where);

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    protected function isColumnName(string $name)
    {
        $parts = explode('.', $name);

        foreach ($parts as $key => $value)
            $parts[$key] = str_replace('`', '', $value);

        $table = count($parts) === 1 ? $this->table : new Schema($parts[0]);

        return $table->hasColumn($parts[0]);
    }

    /**
     * @param Schema|string $table
     * @return $this
     * @throws \Exception
     */
    protected function setTable($table): SqlQueryInterface
    {
        if (!$table instanceof Schema)
            $table = new Schema($table);

        $this->table = $table;

        return $this;
    }
}