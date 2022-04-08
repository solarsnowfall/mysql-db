<?php

namespace Solar\Db\Sql;

interface SqlClauseInterface
{
    public function generateSqlString(): string;

    public function getSqlString(bool $compact = false): string;
}