<?php

namespace Solar\Db;

use Solar\Object\Method\MethodFactory;

class Statement
{
    protected MySql $connection;

    protected \mysqli_stmt $statement;

    /**
     * @param MySql $connection
     * @param string $sql
     */
    public function __construct(MySql $connection, string $sql)
    {
        $this->connection = $connection;

        $this->statement = new \mysqli_stmt($this->connection->getResource(), $sql);
    }

    /**
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->statement->affected_rows;
    }

    /**
     * @param ...$var
     * @return bool
     */
    public function bindResult(&...$var): bool
    {
        return call_user_func_array(array($this->statement, 'bind_result'), $var);
    }

    /**
     * @param mixed $result
     * @return bool
     */
    public function bindResultArray(&$result): bool
    {
        $params = [];

        $meta = $this->statement->result_metadata();

        while ($field = $meta->fetch_field())
            $params[] = &$result[$field->name];

        return call_user_func_array(array($this, 'bindResult'), $params);
    }

    /**
     * @param array $params
     * @param string|null $types
     * @return bool
     */
    public function bindParameters(array $params, string $types = null): bool
    {
        if ($types === null)
            foreach ($params as $parameter)
                $types .= $this->guessParameterType($parameter);

        $args = (array) $types;

        foreach (array_keys($params) as $key)
            $args[] = &$params[$key];

        return call_user_func_array(array($this->statement, 'bind_param'), $args);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->statement->close();
    }

    /**
     * @return int
     */
    public function errorCode(): int
    {
        return $this->statement->errno;
    }

    /**
     * @return string
     */
    public function errorMessage(): string
    {
        return $this->statement->error;
    }

    /**
     * @param array $params
     * @param string|null $types
     * @return $this
     * @throws \Exception
     */
    public function execute(array $params = [], ?string $types = null): Statement
    {
        if (!empty($params))
            $this->bindParameters($params, $types);

        if (!$this->statement->execute())
        {
            throw new \Exception(
                'Unable to execute statement',
                null,
                new \ErrorException($this->errorMessage(), $this->errorCode()),
            );
        }

        return $this;
    }

    /**
     * @return bool|null
     */
    public function fetch(): ?bool
    {
        return $this->statement->fetch();
    }

    /**
     * @return array
     */
    public function fetchAllAssoc()
    {
        $rows = [];

        while ($row = $this->fetchAssoc())
            $rows[] = $row;

        return $rows;
    }

    /**
     * @return array
     */
    public function fetchAssoc()
    {
        $row = [];

        $this->bindResultArray($result);

        if (!$this->fetch())
            return $row;

        foreach ($result as $key => $val)
            $row[$key] = $val;

        return $row;
    }

    /**
     * @return Result
     */
    public function getResult(): Result
    {
        return new Result($this->statement->get_result());
    }

    /**
     * @return mixed
     */
    public function insertId()
    {
        return $this->statement->insert_id;
    }

    /**
     * @return int
     */
    public function numRows(): int
    {
        return $this->statement->num_rows;
    }

    /**
     * @param mixed $parameter
     * @return string
     */
    protected function guessParameterType($parameter): string
    {
        if (ctype_digit((string) $parameter))
            return 'i';

        if (is_numeric($parameter))
            return 'f';

        return 's';
    }
}