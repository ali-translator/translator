<?php

namespace ALI\Translator\Tests\components\Factories;

use PDO;

class PdoFactory
{
    public function generate(): PDO
    {
        $connection = new PDO(SOURCE_MYSQL_DNS, SOURCE_MYSQL_USER, SOURCE_MYSQL_PASSWORD);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }
}
