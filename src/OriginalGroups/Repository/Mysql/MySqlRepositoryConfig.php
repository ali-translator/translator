<?php

namespace ALI\Translator\OriginalGroups\Repository\Mysql;

use PDO;

class MySqlRepositoryConfig
{
    protected PDO $pdo;
    protected string $tableName;

    public function __construct(PDO $pdo, string $tableName = 'ali_original_groups')
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }
}
