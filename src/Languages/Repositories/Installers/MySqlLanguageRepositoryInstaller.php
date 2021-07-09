<?php

namespace ALI\Translator\Languages\Repositories\Installers;

use PDO;

/**
 * Class
 */
class MySqlLanguageRepositoryInstaller
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $languageTableName;

    /**
     * @param PDO $pdo
     * @param string $languageTableName
     */
    public function __construct(PDO $pdo, $languageTableName = 'ali_language')
    {
        $this->pdo = $pdo;
        $this->languageTableName = $languageTableName;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        $query = $this->pdo->prepare(
            'select COUNT(*) from information_schema.tables where table_schema=DATABASE() AND (TABLE_NAME=:tableLanguage)'
        );
        $query->bindValue('tableLanguage', $this->languageTableName);
        $query->execute();

        return (int)$query->fetchColumn() === 1;
    }

    /**
     * Install
     */
    public function install()
    {
        $sqlCommand = 'CREATE TABLE ' . $this->languageTableName . ' (
  alias varchar(4) NOT NULL,
  title varchar(64) NOT NULL DEFAULT \'\',
  is_active tinyint(1) NOT NULL,
  PRIMARY KEY (alias)
)

  ENGINE = INNODB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci';
        $this->pdo->exec($sqlCommand);

        $sqlCommand = 'ALTER TABLE ' . $this->languageTableName . '
ADD UNIQUE INDEX UK_ali_lang_alias (alias);';
        $this->pdo->exec($sqlCommand);
    }

    /**
     * Destroy MySql schema
     */
    public function destroy()
    {
        $this->pdo->exec('DROP table ' . $this->languageTableName);
    }
}
