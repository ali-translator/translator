<?php

namespace ALI\Translator\Tests\components\Factories;

use ALI\Translator\Event\EventDispatcher;
use ALI\Translator\Source\Sources\EventDriven\EventDrivenSource;
use ALI\Translator\Source\Sources\FileSources\CsvSource\CsvFileSource;
use ALI\Translator\Source\Sources\MySqlSource\MySqlSource;
use ALI\Translator\Source\SourceInterface;
use Generator;
use RuntimeException;

class SourceFactory
{
    const SOURCE_MYSQL = 'mysql';
    const SOURCE_CSV = 'csv';
    const SOURCE_EVENT_DRIVEN = 'event_driven';

    public static array $allSourcesTypes = [
        self::SOURCE_MYSQL,
        self::SOURCE_CSV,
        self::SOURCE_EVENT_DRIVEN,
    ];

    /**
     * @return Generator|SourceInterface[]
     */
    public function iterateAllSources(string $originalLanguageAlias, bool $recreate = true): Generator
    {
        foreach (static::$allSourcesTypes as $sourcesType) {
            yield $this->generateSource($sourcesType, $originalLanguageAlias, $recreate);
        }
    }

    public function generateSource($sourceType, $originalLanguageAlias, bool $withDestroy = true): SourceInterface
    {
        switch ($sourceType) {
            case self::SOURCE_CSV:
                $source = new CsvFileSource(SOURCE_CSV_PATH, $originalLanguageAlias);
                break;
            case self::SOURCE_MYSQL:
                $source = new MySqlSource((new PdoFactory())->generate(), $originalLanguageAlias);
                break;
            case self::SOURCE_EVENT_DRIVEN:
                $source = new CsvFileSource(SOURCE_CSV_PATH . '.event_driven', $originalLanguageAlias);
                $eventDispatcher = new EventDispatcher();
                $source = new EventDrivenSource($source, $eventDispatcher);
                break;
            default:
                throw new RuntimeException('Unsupported type: ' . $sourceType);
        }

        $this->installSource($source, $withDestroy);

        return $source;
    }

    public function getSourceTypeBySource(SourceInterface $source): string
    {
        $sourceClasses = [
            MySqlSource::class => self::SOURCE_MYSQL,
            CsvFileSource::class => self::SOURCE_CSV,
            EventDrivenSource::class => self::SOURCE_EVENT_DRIVEN,
        ];

        return $sourceClasses[get_class($source)];
    }

    public function regenerateSource(SourceInterface $source, bool $withDestroy): SourceInterface
    {
        return $this->generateSource($this->getSourceTypeBySource($source), $source->getOriginalLanguageAlias(), $withDestroy);
    }

    protected function installSource(SourceInterface $source, bool $withDestroy = true): void
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
