<?php

namespace Solar\Db;

use Solar\Object\AbstractSingletonFactory;
use Solar\Object\Factory;

class DbConnection extends AbstractSingletonFactory
{
    protected MySql $connection;

    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database,
        int    $port = null,
        string $socket = null,
        int    $flags = null
    ) {

        $connection = Factory::newInstanceOf(MySql::class, get_defined_vars());

        if (!$connection instanceof MySql)
            throw new \Exception("Invalid connection parameters supplied");

        $this->connection = $connection;
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @param string|null $types
     * @return Statement
     * @throws \Exception
     */
    public function execute(string $sql, ?array $params = [], ?string $types = null)
    {
        $statement = $this->connection->prepare($sql);

        $statement->execute($params, $types);

        return $statement;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string|null $types
     * @return array[]
     * @throws \Exception
     */
    public function fetchAllAssoc(string $sql, array $params = [], ?string $types = null): array
    {
        $statement = $this->connection->prepare($sql);

        $statement->execute($params, $types);

        return $statement->fetchAllAssoc();
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string|null $types
     * @return array[]
     * @throws \Exception
     */
    public function fetchAssoc(string $sql, array $params = [], ?string $types = null): array
    {
        $statement = $this->connection->prepare($sql);

        $statement->execute($params, $types);

        return $statement->fetchAssoc();
    }

    public function getInsertId()
    {
        return $this->connection->insertId();
    }

    /**
     * @param array|null $parameters
     * @return DbConnection
     * @throws \Exception
     */
    public static function getInstance(array $parameters = null): DbConnection
    {
        return parent::getInstance($parameters);
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->connection->getDatabase();
    }

    /**
     * @return \mysqli
     */
    public function getResource(): \mysqli
    {
        return $this->connection->getResource();
    }

    /**
     * @param string $sql
     * @return Statement
     * @throws \Exception
     */
    public function prepare(string $sql): Statement
    {
        return $this->connection->prepare($sql);
    }
}