<?php

namespace ALI\Translator\Tests\components\Factories;

use ALI\Translator\Languages\LanguageRepositoryInterface;
use ALI\Translator\Languages\Repositories\ArrayLanguageRepository;
use ALI\Translator\Languages\Repositories\MySqlLanguageRepository;
use Generator;
use RuntimeException;

class LanguageRepositoryFactory
{
    const TYPE_MYSQL = 'mysql';
    const TYPE_ARRAY = 'array';

    public static array  $allTypes = [self::TYPE_MYSQL, self::TYPE_ARRAY];

    /**
     * @param bool $recreate
     * @return Generator|LanguageRepositoryInterface[]
     */
    public function iterateAllRepository(bool $recreate = true): Generator
    {
        foreach (static::$allTypes as $type) {
            yield $this->generateRepository($type, $recreate);
        }
    }

    public function generateRepository(string $type, $withDestroy = true): LanguageRepositoryInterface
    {
        switch ($type) {
            case self::TYPE_ARRAY:
                $repository = new ArrayLanguageRepository();
                break;
            case self::TYPE_MYSQL:
                $repository = new MySqlLanguageRepository((new PdoFactory())->generate());
                break;
            default:
                throw new RuntimeException('Unsupported type: ' . $type);
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
