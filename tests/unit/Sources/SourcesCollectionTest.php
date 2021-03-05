<?php

use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use ALI\Translator\Tests\components\Factories\SourceFactory;
use ALI\Translator\Source\Sources\FileSources\CsvSource\CsvFileSource;
use ALI\Translator\Source\Sources\MySqlSource\MySqlSource;
use ALI\Translator\Source\SourcesCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class SourcesCollectionTest extends TestCase
{
    public function test()
    {
        $sourceFactory = new SourceFactory();

        $sourceCollection = new SourcesCollection();
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_CSV, 'en'),
            ['ua']
        );
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_MYSQL, 'en')
        );

        $this->assertInstanceOf(CsvFileSource::class, $sourceCollection->getSource(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS, 'ua'));
        $this->assertInstanceOf(MySqlSource::class, $sourceCollection->getSource(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS, 'ru'));
        $this->assertInstanceOf(MySqlSource::class, $sourceCollection->getSource(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS, 'cs'));
    }
}
