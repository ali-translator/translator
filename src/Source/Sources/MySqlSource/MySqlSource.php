<?php

namespace ALI\Translator\Source\Sources\MySqlSource;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Installers\MySqlSourceInstaller;
use ALI\Translator\Source\Installers\SourceInstallerInterface;
use ALI\Translator\Source\SourceInterface;
use Exception;
use PDO;
use ALI\Translator\Source\Exceptions\MySqlSource\LanguageNotExistsException;
use PDOStatement;

/**
 * Class
 */
class MySqlSource implements SourceInterface
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    private $originalLanguageAlias;

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
     * @param string $originalLanguageAlias
     * @param string $originalTableName
     * @param string $translateTableName
     */
    public function __construct(
        PDO $pdo,
        string $originalLanguageAlias,
        string $originalTableName = 'ali_original',
        string $translateTableName = 'ali_translate'
    )
    {
        $this->pdo = $pdo;
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->originalTableName = $originalTableName;
        $this->translateTableName = $translateTableName;
    }

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getOriginalLanguageAlias(): string
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @param string $phrase
     * @param string $languageAliasAlias
     * @return string|null
     */
    public function getTranslate(string $phrase, string $languageAliasAlias): ?string
    {
        $translates = $this->getTranslates([$phrase], $languageAliasAlias);

        return $translates[$phrase] ?? null;
    }

    /**
     * @param array $phrases
     * @param string $languageAlias
     * @return string[]
     */
    public function getTranslates(array $phrases, string $languageAlias): array
    {
        if (!$phrases) {
            return [];
        }
        if ($languageAlias === $this->originalLanguageAlias) {
            return array_combine($phrases, $phrases);
        }

        list($whereQuery, $valuesForWhereBinding) = $this->prepareParamsForQuery($phrases, 'select');

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content_index`, o.`content` as `original`, t.`content` as `translate`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
                LEFT JOIN `' . $this->translateTableName . '` AS `t` ON (`o`.`id`=`t`.`original_id` AND `t`.`language_alias`=:translationLanguageAlias)
            WHERE o.`content_index` IN (' . implode(', ', $whereQuery) . ') AND `o`.`language_alias`=:originalLanguageAlias'
            // LIMIT ' . count($phrases) // may be few originals with the same `content_index`
        );
        $dataQuery->bindValue('translationLanguageAlias', $languageAlias, PDO::PARAM_STR);
        $dataQuery->bindValue('originalLanguageAlias', $this->originalLanguageAlias, PDO::PARAM_STR);

        $this->bindParams($valuesForWhereBinding, $dataQuery);

        $dataQuery->execute();

        $phrases = array_flip($phrases);

        $translates = [];
        while ($translateRow = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            if (isset($phrases[$translateRow['original']])) {
                $translates[$translateRow['original']] = $translateRow['translate'];
            }
        }

        return $translates;
    }

    /**
     * Generate keys for find original phrase in database
     * @param string $phrase
     * @return array
     */
    protected function createOriginalQueryParams($phrase)
    {
        $contentIndex = mb_substr($phrase, 0, 64, 'utf8');

        return [
            'contentIndex' => $contentIndex,
            'content' => $phrase,
            'originalLanguageAlias' => $this->originalLanguageAlias,
        ];
    }

    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     * @throws LanguageNotExistsException
     */
    public function saveTranslate(string $languageAlias, string $original, string $translate)
    {
        $originalId = $this->getOriginalId($original);
        if (!$originalId) {
            $originalId = $this->insertOriginal($original);
        }

        $this->saveTranslateByOriginalId($languageAlias, $originalId, $translate);
    }

    /**
     * @param string $original
     * @return mixed
     */
    public function getOriginalId($original)
    {
        $statement = $this->pdo->prepare('
                SELECT id FROM `' . $this->originalTableName . '` WHERE content_index=:contentIndex AND content=:content AND language_alias=:originalLanguageAlias
            ');
        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }
        $statement->execute();
        $originalId = $statement->fetch(PDO::FETCH_COLUMN);

        return $originalId;
    }

    /**
     * @param string $original
     * @return string
     */
    public function insertOriginal($original)
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->originalTableName . '` (`content_index`, `content`, `language_alias`) VALUES (:contentIndex, :content, :originalLanguageAlias)'
        );

        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }

        $statement->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $languageAlias
     * @param int $originalId
     * @param string $translate
     * @throws LanguageNotExistsException
     */
    protected function saveTranslateByOriginalId($languageAlias, $originalId, $translate)
    {
        $updatePdo = $this->pdo->prepare('
                INSERT INTO `' . $this->translateTableName . '` (`original_id`, `language_alias`, `content`)
                VALUES (:id, :translationLanguageAlias, :content)
                ON DUPLICATE KEY UPDATE `content`=:content
            ');
        $updatePdo->bindValue(':content', $translate, PDO::PARAM_STR);
        $updatePdo->bindValue(':id', $originalId, PDO::PARAM_INT);
        $updatePdo->bindValue(':translationLanguageAlias', $languageAlias, PDO::PARAM_STR);
        $updatePdo->execute();
    }

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete(string $original)
    {
        $statement = $this->pdo->prepare('
                DELETE FROM `' . $this->originalTableName . '` WHERE content_index=:contentIndex AND content=:content AND language_alias=:originalLanguageAlias
            ');
        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }
        $statement->execute();
    }

    /**
     * @param string[] $phrases
     * @throws Exception
     */
    public function saveOriginals(array $phrases)
    {
        if (!$phrases) {
            return;
        }
        $phrasesForInsert = array_diff($phrases, $this->getExistOriginals($phrases));
        if (!$phrasesForInsert) {
            return;
        }

        list($valuesQuery, $valuesForWhereBinding) = $this->prepareParamsForQuery($phrasesForInsert, 'insert');

        $statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->originalTableName . '`
                        (`content_index`, `content`,`language_alias`)
                            VALUES ' . implode(',', $valuesQuery) . '
                            '
        );

        $this->bindParams($valuesForWhereBinding, $statement);

        $statement->execute();
    }

    /**
     * @param array $phrases
     * @return array|string[]
     * @throws Exception
     */
    public function getExistOriginals(array $phrases): array
    {
        if (!$phrases) {
            return [];
        }

        list($whereQuery, $valuesForWhereBinding) = $this->prepareParamsForQuery($phrases, 'select');

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content_index`, o.`content` as `original`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
            WHERE o.`content_index` IN (' . implode(', ', $whereQuery) . ')   AND o.`language_alias`=:originalLanguageAlias'
        );
        $dataQuery->bindParam('originalLanguageAlias', $this->originalLanguageAlias, PDO::PARAM_STR);
        $this->bindParams($valuesForWhereBinding, $dataQuery);

        $dataQuery->execute();

        $phrases = array_flip($phrases);

        $existPhrases = [];
        while ($existPhrase = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            if (isset($phrases[$existPhrase['original']])) {
                $existPhrases[] = $existPhrase['original'];
            }
        }

        return $existPhrases;
    }

    public function getOriginalsIds(array $phrases): array
    {
        if (!$phrases) {
            return [];
        }

        [$whereQuery, $valuesForWhereBinding] = $this->prepareParamsForQuery($phrases, 'select');

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content` as `original`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
            WHERE o.`content_index` IN (' . implode(', ', $whereQuery) . ')  AND o.`language_alias`=:originalLanguageAlias'
        );
        $dataQuery->bindParam('originalLanguageAlias', $this->originalLanguageAlias, PDO::PARAM_STR);
        $this->bindParams($valuesForWhereBinding, $dataQuery);

        $dataQuery->execute();

        $phrases = array_flip($phrases);

        $originalsWithIds = [];
        while ($existPhrase = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            if (isset($phrases[$existPhrase['original']])) {
                $originalsWithIds[$existPhrase['original']] = $existPhrase['id'];
            }
        }

        return $originalsWithIds;
    }

    public function getOriginalsByIds(array $originalsIds): array
    {
        $number = 0;
        $originalsIdKeys = [];
        $originalsIdForBinding = [];
        foreach ($originalsIds as $originalId) {
            $number++;
            $valueKey = 'id_' . $number;
            $originalsIdKeys[] = ':'.$valueKey;
            $originalsIdForBinding[$valueKey] = $originalId;
        }

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content` as `original`
                FROM `' . $this->originalTableName . '` AS `o`
            WHERE o.`id` IN (' . implode(', ', $originalsIdKeys) . ')  AND o.`language_alias`=:originalLanguageAlias'
        );
        $dataQuery->bindValue('originalLanguageAlias', $this->originalLanguageAlias, PDO::PARAM_STR);
        foreach ($originalsIdForBinding as $key => $value) {
            $dataQuery->bindValue($key, $value);
        }

        $dataQuery->execute();

        $idsWithOriginals = [];
        while ($existPhrase = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            $originalContent = $existPhrase['original'];
            $originalId = $existPhrase['id'];
            $idsWithOriginals[$originalId] = $originalContent;
        }

        return $idsWithOriginals;
    }

    /**
     * @param array $phrases
     * @param string $type
     * @return array
     */
    private function prepareParamsForQuery(array $phrases, $type)
    {
        $queryParts = [];
        $valuesForWhereBinding = [];
        $queryIndexIncrement = 1;
        foreach ($phrases as $keyForBinding => $phrase) {
            $queryIndexIncrement++;
            $contentIndexKey = 'content_index_' . $queryIndexIncrement;
            $contentKey = 'content_' . $queryIndexIncrement;
            $valuesForWhereBinding[$keyForBinding] = [
                'contentIndexKey' => $contentIndexKey,
                'phrase' => $phrase,
            ];
            switch ($type) {
                case 'select':
                    $queryParts[$keyForBinding] = ':' . $contentIndexKey;
                    break;
                case 'insert':
                    $originalLanguageAliasKey = 'originalLanguageAliasKey_' . $queryIndexIncrement;
                    $valuesForWhereBinding[$keyForBinding]['originalLanguageAliasKey'] = $originalLanguageAliasKey;
                    $valuesForWhereBinding[$keyForBinding]['contentKey'] = $contentKey;
                    $queryParts[$keyForBinding] = '(:' . $contentIndexKey . ', :' . $contentKey . ', :' . $originalLanguageAliasKey . ')';
                    break;
                default:
                    throw new Exception('Invalid type');
                    break;
            }
        }

        return [$queryParts, $valuesForWhereBinding];
    }

    /**
     * @param $valuesForWhereBinding
     * @param PDOStatement $dataQuery
     */
    private function bindParams($valuesForWhereBinding, PDOStatement $dataQuery)
    {
        foreach ($valuesForWhereBinding as $dataForBinding) {
            $originalQueryParams = $this->createOriginalQueryParams($dataForBinding['phrase']);

            if (isset($dataForBinding['contentIndexKey'])) {
                $contentIndexKey = $dataForBinding['contentIndexKey'];
                $contentIndex = $originalQueryParams['contentIndex'];
                $dataQuery->bindValue($contentIndexKey, $contentIndex, PDO::PARAM_STR);
            }

            if (isset($dataForBinding['contentKey'])) {
                $contentKey = $dataForBinding['contentKey'];
                $content = $originalQueryParams['content'];
                $dataQuery->bindValue($contentKey, $content, PDO::PARAM_STR);
            }

            if (isset($dataForBinding['originalLanguageAliasKey'])) {
                $dataQuery->bindValue($dataForBinding['originalLanguageAliasKey'], $this->originalLanguageAlias, PDO::PARAM_STR);
            }
        }
    }

    /**
     * @return MySqlSourceInstaller|SourceInstallerInterface
     */
    public function generateInstaller(): SourceInstallerInterface
    {
        return new MySqlSourceInstaller($this->pdo, $this->originalTableName, $this->translateTableName);
    }

    /**
     * @inheritDoc
     */
    public function getOriginalsWithoutTranslate(string $translationLanguageAlias, $offset = 0, $limit = null): OriginalPhraseCollection
    {
        $originalsWithoutTranslationCollection = new OriginalPhraseCollection($this->originalLanguageAlias);

        $limitSql = [];
        if ($limit) {
            $limitSql[] = 'LIMIT ' . $limit;
        }
        if ($offset) {
            $limitSql[] = 'OFFSET ' . $offset;
        }
        $limitSql = implode(' ', $limitSql);
        $limitSql = $limitSql ? ' ' . $limitSql : null;

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content_index`, o.`content` as `original`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
                LEFT JOIN `' . $this->translateTableName . '` AS `t` ON (`o`.`id`=`t`.`original_id` AND `t`.`language_alias`=:translationLanguageAlias)
            WHERE `t`.original_id IS NULL' . $limitSql
        );
        $dataQuery->bindValue('translationLanguageAlias', $translationLanguageAlias, PDO::PARAM_STR);

        $dataQuery->execute();

        while ($existPhrase = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            $originalsWithoutTranslationCollection->add($existPhrase['original']);
        }

        return $originalsWithoutTranslationCollection;
    }
}
