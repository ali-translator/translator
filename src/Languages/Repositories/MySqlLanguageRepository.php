<?php

namespace ALI\Translator\Languages\Repositories;

use ALI\Translator\Languages\Language;
use ALI\Translator\Languages\LanguageConstructorInterface;
use ALI\Translator\Languages\LanguageInterface;
use ALI\Translator\Languages\LanguageRepositoryInstallerInterface;
use ALI\Translator\Languages\LanguageRepositoryInterface;
use ALI\Translator\Languages\Repositories\Installers\MySqlLanguageRepositoryInstaller;
use \PDO;

class MySqlLanguageRepository implements LanguageRepositoryInterface
{
    protected PDO $pdo;
    protected string $languageTableName;

    /**
     * @var string|LanguageInterface|LanguageConstructorInterface
     */
    protected $languageEntityClass;

    public function __construct(
        PDO $pdo,
        string $languageTableName = 'ali_language',
        string $languageEntityClass = Language::class
    )
    {
        $this->pdo = $pdo;
        $this->languageTableName = $languageTableName;
        $this->languageEntityClass = $languageEntityClass;
    }

    /**
     * @param LanguageInterface $language
     * @param bool $isActive
     * @return bool
     */
    public function save(LanguageInterface $language, bool $isActive): bool
    {
        $statement = $this->pdo->prepare('
                INSERT `' . $this->languageTableName . '` (`is_active`, `alias`, `title`,`iso_code`, `additional_information`) VALUES (:isActive, :alias, :title, :isoCode, :additionalInformation)
                ON DUPLICATE KEY UPDATE `title`=:title, `is_active`=:isActive
            ');
        $statement->bindValue('isActive', (int)$isActive);
        $statement->bindValue('alias', $language->getAlias());
        $statement->bindValue('title', $language->getTitle());
        $statement->bindValue('isoCode', $language->getIsoCode());
        $statement->bindValue('additionalInformation', serialize($language->getAdditionalInformation()));

        return $statement->execute();
    }

    public function find(string $alias): ?LanguageInterface
    {
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '` WHERE alias=:alias LIMIT 1
            ');

        $statement->bindValue('alias', $alias);
        $statement->execute();
        $languageData = $statement->fetch();
        if (!$languageData) {
            return null;
        }

        return $this->generateLanguageObject($languageData);
    }


    public function findAllByAliases(array $aliases): array
    {
        return $this->findAllByColumn('alias', $aliases);
    }

    public function findByIsoCode(string $isoCode): ?LanguageInterface
    {
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '` WHERE iso_code=:isoCode LIMIT 1
            ');

        $statement->bindValue('isoCode', $isoCode);
        $statement->execute();
        $languageData = $statement->fetch();
        if (!$languageData) {
            return null;
        }

        return $this->generateLanguageObject($languageData);
    }

    public function findAllByIsoCodes(array $isoCodes): array
    {
        return $this->findAllByColumn('iso_code', $isoCodes);
    }

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]
     */
    public function getAll(bool $onlyActive): array
    {
        $onlyActive = (int)$onlyActive;
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '`' . ($onlyActive ? ' WHERE is_active=1' : null) . '
            ');
        $statement->execute();

        $languages = [];
        foreach ($statement->fetchAll() as $languageData) {
            $languages[] = $this->generateLanguageObject($languageData);
        }

        return $languages;
    }

    /**
     * @return LanguageInterface[]
     */
    public function getInactiveLanguages(): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM `' . $this->languageTableName . '` WHERE `is_active`=0');
        $statement->execute();

        $languages = [];
        foreach ($statement->fetchAll() as $languageData) {
            $languages[] = $this->generateLanguageObject($languageData);
        }

        return $languages;
    }

    protected function findAllByColumn(string $columnName, array $columnValues): array
    {
        if (empty($columnValues)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($columnValues), '?'));
        $statement = $this->pdo->prepare("SELECT * FROM `{$this->languageTableName}` WHERE {$columnName} IN ({$placeholders})");
        foreach ($columnValues as $index => $columnValue) {
            $statement->bindValue($index + 1, $columnValue);
        }

        $statement->execute();
        $results = $statement->fetchAll();

        if (!$results) {
            return [];
        }

        // Convert result data into LanguageInterface objects
        $languages = [];
        foreach ($results as $languageData) {
            $languages[] = $this->generateLanguageObject($languageData);
        }

        return $languages;
    }

    protected function generateLanguageObject(array $languageData): LanguageInterface
    {
        if (isset($languageData['additional_information'])) {
            $additionalInformation = unserialize($languageData['additional_information']);
        } else {
            $additionalInformation = [];
        }

        return new $this->languageEntityClass(
            $languageData['iso_code'],
            $languageData['title'],
            $languageData['alias'],
            $additionalInformation
        );
    }

    public function generateInstaller(): LanguageRepositoryInstallerInterface
    {
        return new MySqlLanguageRepositoryInstaller($this->pdo, $this->languageTableName);
    }
}
