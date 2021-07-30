<?php

namespace ALI\Translator\Tests\components\Factories;

use ALI\Translator\OriginalGroups\OriginalGroupRepositoryInterface;
use ALI\Translator\OriginalGroups\Repository\Mysql\MysqlOriginalGroupRepository;
use ALI\Translator\OriginalGroups\Repository\Mysql\MySqlRepositoryConfig;
use PDO;

class OriginalGroupRepositoryFactory
{
    /**
     * @return OriginalGroupRepositoryInterface[]
     */
    public function getGroupRepositories(): array
    {
        return [
            $this->generateMysqlRepository(),
        ];
    }

    public function generateMysqlRepository(): OriginalGroupRepositoryInterface
    {
        $translatorSource = (new SourceFactory())->generateSource(SourceFactory::SOURCE_CSV, 'en', true);
        $mysqlConfig = new MySqlRepositoryConfig($this->createPDO());

        return new MysqlOriginalGroupRepository($mysqlConfig, $translatorSource);
    }

    /**
     * @return PDO
     */
    protected function createPDO(): PDO
    {
        $connection = new PDO(SOURCE_MYSQL_DNS, SOURCE_MYSQL_USER, SOURCE_MYSQL_PASSWORD);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }
}
