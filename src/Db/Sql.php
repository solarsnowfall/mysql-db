<?php

namespace Solar\Db;

class Sql
{
    protected DbConnection $db;

    public function __construct(DbConnection $db)
    {
        $this->db = $db;
    }
}