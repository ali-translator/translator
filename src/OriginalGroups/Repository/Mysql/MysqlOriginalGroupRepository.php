<?php

namespace ALI\Translator\OriginalGroups\Repository\Mysql;

use ALI\Translator\OriginalGroups\OriginalGroupRepositoryInstallerInterface;
use ALI\Translator\OriginalGroups\OriginalGroupRepositoryInterface;
use ALI\Translator\Source\SourceInterface;
use PDO;
use PDOStatement;

class MysqlOriginalGroupRepository implements OriginalGroupRepositoryInterface
{
    /**
     * @var MySqlRepositoryConfig
     */
    protected $mySqlRepositoryConfig;

    /**
     * @var SourceInterface
     */
    protected $translatorSource;

    /**
     * @param MySqlRepositoryConfig $mySqlRepositoryConfig
     * @param SourceInterface $translatorSource
     */
    public function __construct(MySqlRepositoryConfig $mySqlRepositoryConfig, SourceInterface $translatorSource)
    {
        $this->mySqlRepositoryConfig = $mySqlRepositoryConfig;
        $this->translatorSource = $translatorSource;
    }

    public function addGroups(array $originals, array $groups)
    {
        if (!$originals || !$groups) {
            return;
        }

        $originalsIds = $this->translatorSource->getOriginalsIds($originals);
        if (!$originalsIds) {
            return;
        }

        list($valuesQuery, $valuesForWhereBinding) = $this->prepareParamsForInserQuery($originalsIds, $groups);

        $statement = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'INSERT IGNORE  INTO `' . $this->mySqlRepositoryConfig->getTableName() . '`
                        (`group_alias`,`language_alias`,`original_id`)
                            VALUES ' . implode(',', $valuesQuery)
        );

        $this->bindParams($valuesForWhereBinding, $statement);

        $statement->execute();
    }

    /**
     * @param $valuesForWhereBinding
     * @param PDOStatement $dataQuery
     */
    private function bindParams($valuesForWhereBinding, PDOStatement $dataQuery)
    {
        foreach ($valuesForWhereBinding as $dataForBinding) {
            $groupKey = $dataForBinding['groupKey'];
            $group = $dataForBinding['group'];
            $originalIdKey = $dataForBinding['originalIdKey'];
            $originalId = $dataForBinding['originalId'];

            $dataQuery->bindValue($groupKey, $group, PDO::PARAM_STR);
            $dataQuery->bindValue($originalIdKey, $originalId, PDO::PARAM_STR);

            if (isset($dataForBinding['originalLanguageAliasKey'])) {
                $dataQuery->bindValue($dataForBinding['originalLanguageAliasKey'], $this->translatorSource->getOriginalLanguageAlias(), PDO::PARAM_STR);
            }
        }
    }

    public function getOriginalsByGroup(string $groupAlias, int $offset = 0, int $limit = 100): array
    {
        $tableName = $this->mySqlRepositoryConfig->getTableName();
        $dataQuery = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'SELECT `original_content`
                FROM `' . $tableName . '`
            WHERE `language_alias`=:originalLanguageAlias AND `group_alias`=:groupAlias
            LIMIT ' . $limit . ',' . $offset
        );
        $dataQuery->bindParam('originalLanguageAlias', $originalLanguageAlias, PDO::PARAM_STR);
        $dataQuery->bindParam('groupAlias', $groupAlias, PDO::PARAM_STR);

        $dataQuery->execute();

        $originals = [];
        while ($existPhrase = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            $originals[] = $existPhrase['original_content'];
        }

        return $originals;
    }

    public function getGroups(array $originalsContents): array
    {
        if (!$originalsContents) {
            return [];
        }
        $originalsId = $this->getTranslatorSource()->getOriginalsIds($originalsContents);
        if (!$originalsId) {
            return [];
        }

        list($originalsContentsWhere, $originalsContentsForBinding) = $this->prepareInCondition($originalsId, 'original_id');

        $tableName = $this->mySqlRepositoryConfig->getTableName();
        $dataQuery = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'SELECT `original_id`, `group_alias`
                FROM `' . $tableName . '`
            WHERE `language_alias`=:originalLanguageAlias AND `original_id` IN (' . implode(',', $originalsContentsWhere) . ')'
        );
        $dataQuery->bindValue('originalLanguageAlias', $this->translatorSource->getOriginalLanguageAlias(), PDO::PARAM_STR);
        foreach ($originalsContentsForBinding as $key => $value) {
            $dataQuery->bindValue($key, $value, PDO::PARAM_STR);
        }

        $dataQuery->execute();

        $resultData = $dataQuery->fetchAll(PDO::FETCH_ASSOC);

        $originalsGroups = [];
        $originalsIndexedById = array_combine($originalsId, array_keys($originalsId));
        foreach ($resultData as $resultDataItem) {
            $originalId = $resultDataItem['original_id'];
            $originalContent = $originalsIndexedById[$originalId];

            if (!isset($originalsGroups[$originalContent])) {
                $originalsGroups[$originalContent] = [];
            }
            $group = $resultDataItem['group_alias'];
            $originalsGroups[$originalContent][] = $group;
        }

        return $originalsGroups;
    }

    public function removeGroups(array $originals, array $groups)
    {
        if (!$originals || !$groups) {
            return;
        }
        $originalsId = $this->getTranslatorSource()->getOriginalsIds($originals);
        if (!$originalsId) {
            return;
        }

        list($originalsContentsWhere, $originalsContentsForBinding) = $this->prepareInCondition($originalsId, 'original_id');
        list($groupsWhere, $groupsForBinding) = $this->prepareInCondition($groups, 'group');

        $tableName = $this->mySqlRepositoryConfig->getTableName();
        $dataQuery = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'DELETE FROM `' . $tableName . '`
            WHERE `language_alias`=:originalLanguageAlias AND `original_id` IN (' . implode(',', $originalsContentsWhere) . ')
            AND group_alias IN (' . implode(',', $groupsWhere) . ') '
        );
        $dataQuery->bindValue('originalLanguageAlias', $this->translatorSource->getOriginalLanguageAlias(), PDO::PARAM_STR);
        foreach (array_merge($originalsContentsForBinding, $groupsForBinding) as $key => $value) {
            $dataQuery->bindValue($key, $value, PDO::PARAM_STR);
        }

        $dataQuery->execute();
    }

    public function removeAllGroups(array $originals)
    {
        if (!$originals) {
            return;
        }
        $originalsId = $this->getTranslatorSource()->getOriginalsIds($originals);
        if (!$originalsId) {
            return;
        }

        list($originalsContentsWhere, $originalsContentsForBinding) = $this->prepareInCondition($originalsId, 'original_id');

        $tableName = $this->mySqlRepositoryConfig->getTableName();
        $dataQuery = $this->mySqlRepositoryConfig->getPdo()->prepare(
            'DELETE FROM `' . $tableName . '`
            WHERE `language_alias`=:originalLanguageAlias AND `original_id` IN (' . implode(',', $originalsContentsWhere) . ')'
        );
        $dataQuery->bindValue('originalLanguageAlias', $this->translatorSource->getOriginalLanguageAlias(), PDO::PARAM_STR);
        foreach ($originalsContentsForBinding as $key => $value) {
            $dataQuery->bindValue($key, $value, PDO::PARAM_STR);
        }

        $dataQuery->execute();
    }

    public function generateInstaller(): OriginalGroupRepositoryInstallerInterface
    {
        return new MysqlOriginalGroupRepositoryInstaller($this->mySqlRepositoryConfig);
    }

    public function getTranslatorSource(): SourceInterface
    {
        return $this->translatorSource;
    }

    protected function prepareInCondition(array $data, string $alias)
    {
        $queryParts = [];
        $valuesForBinding = [];

        $increment = 0;
        foreach ($data as $value) {
            $sqlKey = $alias . '_' . $increment;
            $queryParts[] = ':' . $sqlKey;
            $valuesForBinding[$sqlKey] = $value;
            $increment++;
        }

        return [$queryParts, $valuesForBinding];
    }

    protected function prepareParamsForInserQuery(array $originalsId, array $groups)
    {
        $queryParts = [];
        $valuesForWhereBinding = [];
        $queryIndexIncrement = 1;
        foreach ($originalsId as $originalId) {
            foreach ($groups as $group) {
                $queryIndexIncrement++;
                $originalIdKey = 'originalsContent_' . $queryIndexIncrement;
                $groupKey = 'group_' . $queryIndexIncrement;
                $valuesForWhereBinding[$queryIndexIncrement] = [
                    'originalId' => $originalId,
                    'originalIdKey' => $originalIdKey,
                    'group' => $group,
                    'groupKey' => $groupKey,
                ];

                $originalLanguageAliasKey = 'originalLanguageAliasKey_' . $queryIndexIncrement;
                $valuesForWhereBinding[$queryIndexIncrement]['originalLanguageAliasKey'] = $originalLanguageAliasKey;
                $queryParts[$queryIndexIncrement] = '(:' . $groupKey . ', :' . $originalLanguageAliasKey . ',:' . $originalIdKey . ')';
            }
        }

        return [$queryParts, $valuesForWhereBinding];
    }
}
