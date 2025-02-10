<?php

namespace ALI\Translator\OriginalGroups\Repository\Mysql;

use ALI\Translator\OriginalGroups\OriginalGroupRepositoryInstallerInterface;

class MysqlOriginalGroupRepositoryInstaller implements OriginalGroupRepositoryInstallerInterface
{
    protected MySqlRepositoryConfig $mySqlRepositoryConfig;

    public function __construct(MySqlRepositoryConfig $mySqlRepositoryConfig)
    {
        $this->mySqlRepositoryConfig = $mySqlRepositoryConfig;
    }

    public function isInstalled(): bool
    {
        $query = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'select COUNT(*) from information_schema.tables where table_schema=DATABASE() AND (TABLE_NAME=:tableName)'
        );
        $query->bindValue('tableName', $this->mySqlRepositoryConfig->getTableName());
        $query->execute();

        return (int)$query->fetchColumn() === 1;
    }

    public function install()
    {
        $sqlCommand = 'CREATE TABLE ' . $this->mySqlRepositoryConfig->getTableName() . ' (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  language_alias CHAR(2) NOT NULL,
  original_id int(11) UNSIGNED NOT NULL,
  group_alias varchar(100) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

ALTER TABLE ' . $this->mySqlRepositoryConfig->getTableName() . '
ADD UNIQUE INDEX UK_aog__original__language (original_id, language_alias, group_alias);';
        $this->mySqlRepositoryConfig->getPdo()->exec($sqlCommand);
    }

    /**
     * Destroy MySql schema
     */
    public function destroy()
    {
        $this->mySqlRepositoryConfig->getPdo()->exec('DROP table ' . $this->mySqlRepositoryConfig->getTableName());
    }
}
