<?php

namespace ALI\Translator\Tests\components\Factories;

use ALI\Translator\Languages\LanguageRepositoryInterface;
use ALI\Translator\Languages\Repositories\ArrayLanguageRepository;
use ALI\Translator\Languages\Repositories\MySqlLanguageRepository;
use ALI\Translator\Source\SourceInterface;

class LanguageRepositoryFactory
{
    const TYPE_MYSQL = 'mysql';
    const TYPE_ARRAY = 'array';

    public static $allTypes = [self::TYPE_MYSQL, self::TYPE_ARRAY];

    /**
     * @param $originalLanguageAlias
     * @param bool $recreate
     * @return \Generator|SourceInterface[]
     */
    public function iterateAllRepository($originalLanguageAlias, $recreate = true)
    {
        foreach (static::$allTypes as $type) {
            yield $this->generateRepository($type, $recreate);
        }
    }

    public function generateRepository(string $type, $withDestroy = true)
    {
        switch ($type) {
            case self::TYPE_ARRAY:
                $repository = new ArrayLanguageRepository();
                break;
            case self::TYPE_MYSQL:
                $repository = new MySqlLanguageRepository((new PdoFactory())->generate());
                break;
        }

        $this->install($repository, $withDestroy);

        return $repository;
    }

    protected function install(LanguageRepositoryInterface $languageRepository, $withDestroy = true)
    {
        $installer = $languageRepository->generateInstaller();
        $needInstall = true;
        if ($installer->isInstalled()) {
            if ($withDestroy) {
                $installer->destroy();
            } else {
                $needInstall = false;
            }
        }
        if ($needInstall) {
            $installer->install();
        }
    }
}
