<?php

namespace ALI\Translator\Tests\unit\Sources;

use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use ALI\Translator\Tests\components\Factories\SourceFactory;
use ALI\Translator\Tests\components\SourceTester;
use ALI\Translator\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\Source\Exceptions\MySqlSource\LanguageNotExistsException;
use ALI\Translator\Source\Exceptions\SourceException;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    /**
     * @throws SourceException
     */
    public function test()
    {
        $originalLanguageAlias = LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS;

        $this->checkSources($originalLanguageAlias);
        $this->checkSavedStateSources($originalLanguageAlias);
    }

    /**
     * @param string $originalLanguageAlias
     * @throws SourceException
     */
    private function checkSources($originalLanguageAlias)
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory->iterateAllSources($originalLanguageAlias) as $source) {
            $sourceTester = new SourceTester();
            $sourceTester->testSource($source, $this);
            $source->generateInstaller()->destroy();
        }
    }

    /**
     * @param string $originalLanguageAlias
     * @throws SourceException
     */
    private function checkSavedStateSources($originalLanguageAlias)
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory->iterateAllSources($originalLanguageAlias) as $source) {
            // Save
            $source->saveOriginals(['Happy New Year!']);
            $source->saveTranslate(LanguagesEnum::TRANSLATION_LANGUAGE_ALIAS, 'What\'s happening?', 'Що відбувається?');

            // Get new instance from saved state
            $source = $sourceFactory->regenerateSource($source, false);
            $translate = $source->getTranslate('What\'s happening?', LanguagesEnum::TRANSLATION_LANGUAGE_ALIAS);
            $this->assertEquals('Що відбувається?', $translate);
            $this->assertEquals(['Happy New Year!'], $source->getExistOriginals(['Happy New Year!']));
            $this->assertEquals([], $source->getExistOriginals(['Happy birthday!']));

            $source->generateInstaller()->destroy();
        }
    }

    /**
     * test SourceInstaller
     */
    public function testSourceInstaller()
    {
        foreach ((new SourceFactory())->iterateAllSources(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS) as $source) {
            $installer = $source->generateInstaller();

            $this->assertTrue($installer->isInstalled());
            $installer->destroy();
            $this->assertFalse($installer->isInstalled());
            $installer->install();
            $this->assertTrue($installer->isInstalled());
        }
    }

    /**
     * Test few original languages on one Source
     *
     * @throws SourceException
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     * @throws LanguageNotExistsException
     */
    public function testMultiOriginalLanguagesSources()
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory::$allSourcesTypes as $sourceType) {
            $firstSource = $sourceFactory->generateSource($sourceType, 'en', true);
            $secondSource = $sourceFactory->generateSource($sourceType, 'de', true);

            $phraseOriginal = 'Hello';

            // Without saved original
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);

            // With saved original
            $firstSource->saveOriginals([$phraseOriginal]);
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            // With saved translate on first source
            $firstSource->saveTranslate('ua', $phraseOriginal, 'Привіт');
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт', $phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            // With saved translate on second source
            $secondSource->saveTranslate('ua', $phraseOriginal, 'Привіт1');
            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт1', $phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт', $phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            // Delete on first source
            $firstSource->delete($phraseOriginal);
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт1', $phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            // Delete on second source
            $secondSource->delete($phraseOriginal);
            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);
        }
    }

    public function testGettingOriginalsWithoutTranslate()
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory::$allSourcesTypes as $sourceType) {
            $source = $sourceFactory->generateSource($sourceType, 'en', true);

            $source->saveOriginals([
                'Hello {name}',
                'Hi {name}',
                'He has bigger fish to fry',
                'Good things come to those who wait',
            ]);

            $source->saveTranslate('ua', 'Hello {name}', 'Привіт {name}');
            $source->saveTranslate('ua', 'Good things come to those who wait', 'Наберіться терпіння');

            $originalsWithoutTranslate = $source->getOriginalsWithoutTranslate('ua');
            $this->assertEquals([
                'Hi {name}',
                'He has bigger fish to fry',
            ], $originalsWithoutTranslate->getAll());

            $originalsWithoutTranslate = $source->getOriginalsWithoutTranslate('ua', 0, 1);
            $this->assertEquals([
                'Hi {name}',
            ], $originalsWithoutTranslate->getAll());

            $originalsWithoutTranslate = $source->getOriginalsWithoutTranslate('ua', 1, 1);
            $this->assertEquals([
                'He has bigger fish to fry',
            ], $originalsWithoutTranslate->getAll());
        }
    }

    public function testWorkWithIds()
    {
        $originals = [
            'Hello {name}',
            'Hi {name}',
            'He has bigger fish to fry',
            'Good things come to those who wait',
        ];

        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory::$allSourcesTypes as $sourceType) {
            $source = $sourceFactory->generateSource($sourceType, 'en', true);

            $originalsIds = $source->getOriginalsIds($originals);
            static::assertEmpty($originalsIds);

            $source->saveOriginals($originals);

            $originalsIds = $source->getOriginalsIds([]);
            static::assertEmpty($originalsIds);

            $originalsIds = $source->getOriginalsIds($originals);
            static::assertCount(count($originals), $originalsIds);
            $originalsIdsKeys = array_keys($originalsIds);
            static::assertEmpty(array_diff($originalsIdsKeys, $originals));

            $searchedOriginals = $source->getOriginalsByIds($originalsIds);
            static::assertCount(count($originals), $searchedOriginals);
            static::assertEmpty(array_diff($searchedOriginals, $originals));

            foreach ($originals as $original) {
                $source->delete($original);
            }

            $originalsIds = $source->getOriginalsIds($originals);
            static::assertEmpty($originalsIds);
        }
    }
}
