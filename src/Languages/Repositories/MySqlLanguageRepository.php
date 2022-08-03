<?php

namespace ALI\Translator\Languages\Repositories;

use ALI\Translator\Languages\Language;
use ALI\Translator\Languages\LanguageInterface;
use ALI\Translator\Languages\LanguageRepositoryInstallerInterface;
use ALI\Translator\Languages\LanguageRepositoryInterface;
use ALI\Translator\Languages\Repositories\Installers\MySqlLanguageRepositoryInstaller;
use \PDO;

/**
 * MySqlLanguageRepository
 */
class MySqlLanguageRepository implements LanguageRepositoryInterface
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

    /**
     * @param string $alias
     * @return LanguageInterface|null
     */
    public function find(string $alias)
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

    /**
     * @param string $alias
     * @return LanguageInterface|null
     */
    public function findByIsoCode(string $isoCode)
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

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]|array
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

    /**
     * @param array $languageData
     * @return Language
     */
    protected function generateLanguageObject(array $languageData)
    {
        if (isset($languageData['additional_information'])) {
            $additionalInformation = unserialize($languageData['additional_information']);
        } else {
            $additionalInformation = [];
        }

        return new Language(
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
