<?php

namespace ALI\Translator\Tests\components\Factories;

use ALI\Translator\Source\Sources\FileSources\CsvSource\CsvFileSource;
use ALI\Translator\Source\Sources\MySqlSource\MySqlSource;
use ALI\Translator\Source\SourceInterface;
use PDO;

/**
 * SourceFactory
 */
class SourceFactory
{
    const SOURCE_MYSQL = 'mysql';
    const SOURCE_CSV = 'csv';

    public static $allSourcesTypes = [self::SOURCE_MYSQL, self::SOURCE_CSV];

    /**
     * @param $originalLanguageAlias
     * @param bool $recreate
     * @return \Generator|SourceInterface[]
     */
    public function iterateAllSources($originalLanguageAlias, $recreate = true)
    {
        foreach (static::$allSourcesTypes as $sourcesType) {
            yield $this->generateSource($sourcesType, $originalLanguageAlias, $recreate);
        }
    }

    /**
     * @param $originalLanguageAlias
     * @param $sourceType
     * @param bool $withDestroy
     * @return CsvFileSource|MySqlSource
     */
    public function generateSource($sourceType, $originalLanguageAlias, $withDestroy = true)
    {
        switch ($sourceType) {
            case self::SOURCE_CSV:
                $source = new CsvFileSource(SOURCE_CSV_PATH, $originalLanguageAlias);
                break;
            case self::SOURCE_MYSQL:
                $source = new MySqlSource((new PdoFactory())->generate(), $originalLanguageAlias);
                break;
        }

        $this->installSource($source, $withDestroy);

        return $source;
    }

    /**
     * @param SourceInterface $source
     * @return string
     */
    public function getSourceTypeBySource($source): string
    {
        $sourceClasses = [
            MySqlSource::class => self::SOURCE_MYSQL,
            CsvFileSource::class => self::SOURCE_CSV,
        ];

        return $sourceClasses[get_class($source)];
    }

    /**
     * @param SourceInterface $sourcedump
     * @return SourceInterface
     */
    public function regenerateSource($source, $withDestroy)
    {
        return $this->generateSource($this->getSourceTypeBySource($source), $source->getOriginalLanguageAlias(), $withDestroy);
    }

    protected function installSource(SourceInterface $source, $withDestroy = true)
    {
        $sourceInstaller = $source->generateInstaller();
        $needInstall = true;
        if ($sourceInstaller->isInstalled()) {
            if ($withDestroy) {
                $sourceInstaller->destroy();
            } else {
                $needInstall = false;
            }
        }
        if ($needInstall) {
            $sourceInstaller->install();
        }
    }
}
