<?php

namespace ALI\Translator\OriginalGroups\Repository\Mysql;

use ALI\Translator\OriginalGroups\OriginalGroupRepositoryInstallerInterface;

class MysqlOriginalGroupRepositoryInstaller implements OriginalGroupRepositoryInstallerInterface
{
    /**
     * @var MySqlRepositoryConfig
     */
    protected $mySqlRepositoryConfig;

    public function __construct(MySqlRepositoryConfig $mySqlRepositoryConfig)
    {
        $this->mySqlRepositoryConfig = $mySqlRepositoryConfig;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        $query = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'select COUNT(*) from information_schema.tables where table_schema=DATABASE() AND (TABLE_NAME=:tableName)'
        );
        $query->bindValue('tableName', $this->mySqlRepositoryConfig->getTableName());
        $query->execute();

        return (int)$query->fetchColumn() === 1;
    }

    /**
     * Install
     */
    public function install()
    {
        $sqlCommand = 'CREATE TABLE ' . $this->mySqlRepositoryConfig->getTableName() . ' (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  language_alias CHAR(2) NOT NULL,
  original_id TEXT NOT NULL,
  group_alias varchar(20) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

ALTER TABLE ' . $this->mySqlRepositoryConfig->getTableName() . '
ADD UNIQUE INDEX UK_aog__original__language (original_id, language_alias);';
        $this->mySqlRepositoryConfig->getPdo()->exec($sqlCommand);
    }

    /**
     * Destroy MySql ALI schema
     */
    public function destroy()
    {
        $this->mySqlRepositoryConfig->getPdo()->exec('DROP table ' . $this->mySqlRepositoryConfig->getTableName());
    }
}
