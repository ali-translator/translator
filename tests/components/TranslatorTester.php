<?php

namespace ALI\Translator\Tests\components;

use ALI\Translator\Source\Sources\FileSources\CsvSource\CsvFileSource;
use ALI\Translator\Source\Sources\MySqlSource\MySqlSource;
use ALI\Translator\Source\SourcesCollection;
use ALI\Translator\Tests\components\Factories\SourceFactory;
use ALI\Translator\TranslatorInterface;
use Exception;
use PHPUnit\Framework\TestCase;

class TranslatorTester
{
    /**
     * @return SourcesCollection
     */
    public function generateSourceCollection()
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
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_MYSQL, 'de'),
            ['en']
        );

        return $sourceCollection;
    }

    /**
     * @param TranslatorInterface $translator
     * @param TestCase $testCase
     */
    public function test($translator, $testCase)
    {
        $this->testSourceResolving($translator, $testCase);
        $this->testTranslating($translator, $testCase);
    }

    /**
     * @param TranslatorInterface $translator
     * @param TestCase $testCase
     */
    public function testSourceResolving($translator, $testCase)
    {
        $source = $translator->getSource('en', 'ua');
        $testCase->assertInstanceOf(CsvFileSource::class, $source);
        $source = $translator->getSource('en', 'ru');
        $testCase->assertInstanceOf(MySqlSource::class, $source);
        $source = $translator->getSource('en', 'de');
        $testCase->assertInstanceOf(MySqlSource::class, $source);
        $source = $translator->getSource('de', 'en');
        $testCase->assertInstanceOf(MySqlSource::class, $source);

        try {
            $translator->getSource('de', 'ua');
        } catch (Exception $exception) {
        } finally {
            $testCase->assertInstanceOf(Exception::class, $exception);
            unset($exception);
        }

        try {
            $translator->getSource('be', 'en');
        } catch (Exception $exception) {
        } finally {
            $testCase->assertInstanceOf(Exception::class, $exception);
            unset($exception);
        }
    }

    /**
     * @param TranslatorInterface $translator
     * @param TestCase $testCase
     */
    public function testTranslating($translator, $testCase)
    {
        $phraseOriginal = 'Hello';

        // Without saved original
        $phraseTranslate = $translator->translate('en', 'ua', $phraseOriginal, false);
        $testCase->assertNull($phraseTranslate);
        $phraseTranslate = $translator->translate('en', 'ru', $phraseOriginal, false);
        $testCase->assertNull($phraseTranslate);

        // With saved original
        $translator->getSource('en')->saveOriginals([$phraseOriginal]);
        $phraseTranslate = $translator->translate('en', 'ua', $phraseOriginal, false);
        $testCase->assertNull($phraseTranslate);
        $phraseTranslate = $translator->translate('en', 'ru', $phraseOriginal, false);
        $testCase->assertNull($phraseTranslate);

        // With ua translate
        $translator->saveTranslate('en', 'ua', $phraseOriginal, 'Привіт');
        $phraseTranslate = $translator->translate('en', 'ua', $phraseOriginal, false);
        $testCase->assertEquals('Привіт', $phraseTranslate);
        $phraseTranslate = $translator->translate('en', 'ru', $phraseOriginal, false);
        $testCase->assertNull($phraseTranslate);
        $translator->delete('en', $phraseOriginal, 'ua');

        // With ru translate
        $translator->saveTranslate('en', 'ru', $phraseOriginal, 'Привет');
        $phraseTranslate = $translator->translate('en', 'ua', $phraseOriginal, false);
        $testCase->assertNull($phraseTranslate);
        $phraseTranslate = $translator->translate('en', 'ru', $phraseOriginal, false);
        $testCase->assertEquals('Привет', $phraseTranslate);
    }
}
