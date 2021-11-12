<?php

namespace ALI\Translator\Source\Installers;

use PDO;

/**
 * MySqlSourceInstaller
 */
class MySqlSourceInstaller implements SourceInstallerInterface
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $originalTableName;

    /**
     * @var string
     */
    protected $translateTableName;

    /**
     * @param PDO $pdo
     * @param string $originalTableName
     * @param string $translateTableName
     */
    public function __construct(PDO $pdo, $originalTableName = 'ali_original', $translateTableName = 'ali_translate')
    {
        $this->pdo = $pdo;
        $this->originalTableName = $originalTableName;
        $this->translateTableName = $translateTableName;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        $query = $this->pdo->prepare(
            'select COUNT(*) from information_schema.tables where table_schema=DATABASE() AND (TABLE_NAME=:tableOriginal OR TABLE_NAME=:tableTranslate)'
        );
        $query->bindValue('tableOriginal', $this->originalTableName);
        $query->bindValue('tableTranslate', $this->translateTableName);
        $query->execute();

        return (int)$query->fetchColumn() === 2;
    }

    /**
     * Install
     */
    public function install()
    {
        $this->installOriginalTable();
        $this->installTranslateTable();
    }

    /**
     * Install original table
     */
    private function installOriginalTable()
    {
        $sqlCommand = 'CREATE TABLE ' . $this->originalTableName . ' (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  language_alias CHAR(2) NOT NULL,
  content_index VARCHAR(64) NOT NULL COMMENT \'System column for indexation\',
  content TEXT NOT NULL,
  PRIMARY KEY (id),
  INDEX indexContentIndex (content_index)
)
  ENGINE = INNODB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_bin';
        $this->pdo->exec($sqlCommand);
    }

    /**
     * Install original table
     */
    private function installTranslateTable()
    {
        $sqlCommand = 'CREATE TABLE ' . $this->translateTableName . ' (
  original_id INT(11) UNSIGNED    NOT NULL,
  language_alias VARCHAR(4) NOT NULL,
  content     TEXT                NOT NULL,
  PRIMARY KEY (original_id, language_alias),
  INDEX IDX_ali_translate_original_id (original_id),
  CONSTRAINT FK_ali_translate_ali_original_id FOREIGN KEY (original_id)
  REFERENCES ali_original (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = INNODB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci';
        $this->pdo->exec($sqlCommand);
    }

    /**
     * Destroy MySql ALI schema
     */
    public function destroy()
    {
        $sqlCommand = [
            'DROP table ' . $this->translateTableName,
            'DROP table ' . $this->originalTableName,
        ];
        foreach ($sqlCommand as $sqlCommand) {
            $this->pdo->exec($sqlCommand);
        }
    }
}
