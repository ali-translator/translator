<?php

namespace ALI\Translator\OriginalGroups\Repository\Mysql;

use PDO;

class MySqlRepositoryConfig
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param PDO $pdo
     * @param string $tableName
     */
    public function __construct(PDO $pdo, string $tableName = 'ali_original_groups')
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
}
