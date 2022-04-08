<?php

namespace Solar\Db;

class Result
{
    protected \mysqli_result $result;

    /**
     * @param \mysqli_result
     */
    public function __construct(\mysqli_result $result)
    {
        $this->result = $result;
    }

    /**
     * @return void
     */
    public function close()
    {
        $this->result->close();
    }

    /**
     * @return array
     */
    public function fetchAllAssoc(): array
    {
        $rows = [];

        while ($row = $this->fetchAssoc())
            $rows[] = $row;

        return $rows;
    }

    /**
     * @return array|null
     */
    public function fetchAssoc(): ?array
    {
        return $this->result->fetch_assoc();
    }

    /**
     * @return int
     */
    public function numRows(): int
    {
        return $this->result->num_rows;
    }
}