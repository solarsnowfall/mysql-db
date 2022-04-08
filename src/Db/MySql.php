<?php

namespace Solar\Db;

use Solar\Object\Method\MethodFactory;

class MySql
{
    protected array $connectionParams = [];

    protected ?\mysqli $resource = null;

    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database,
        int $port = null,
        string $socket = null,
        int $flags = null
    ) {

        $this->connectionParams = get_defined_vars();

        $this->connect();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function connect()
    {
        if ($this->resource instanceof \mysqli)
            return $this;

        $this->resource = new \mysqli();

        $this->resource->init();

        MethodFactory::invoke($this->resource, 'real_connect', $this->connectionParams);

        if ($this->resource->connect_error)
        {
            throw new \Exception(
                'Connection error: ',
                null,
                new \ErrorException($this->resource->connect_error, $this->resource->connect_errno)
            );
        }

        return $this;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string|null $types
     * @return Statement
     * @throws \Exception
     */
    public function execute(string $sql, array $params = [], ?string $types = null): Statement
    {
        $statement = $this->prepare($sql);

        $statement->execute($params, $types);

        return $statement;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->connectionParams['database'];
    }

    /**
     * @return \mysqli
     */
    public function getResource(): \mysqli
    {
        return $this->resource;
    }

    /**
     * @return mixed
     */
    public function insertId()
    {
        return $this->resource->insert_id;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return ($this->resource instanceof \mysqli);
    }

    /**
     * @param string $sql
     * @return Statement
     * @throws \Exception
     */
    public function prepare(string $sql): Statement
    {
        if (!$this->isConnected())
            $this->connect();

        return new Statement($this, $sql);
    }
}