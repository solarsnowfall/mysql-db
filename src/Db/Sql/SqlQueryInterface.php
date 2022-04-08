<?php

namespace Solar\Db\Sql;

use Solar\Db\Statement;

interface SqlQueryInterface
{
    public function execute(): Statement;

    public function from($table): SqlQueryInterface;

    public function getSqlString(bool $compact = false): string;

    public function into($table): SqlQueryInterface;

    public function join($table, $on, $type = null): SqlQueryInterface;

    public function limit(array $limit): SqlQueryInterface;

    public function orderBy(array $orderBy): SqlQueryInterface;

    public function where(array $where): SqlQueryInterface;
}