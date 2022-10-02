<?php

namespace ALI\Translator\Tests\unit\MissingTranslateCallbacks;

use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use ALI\Translator\Tests\components\Factories\SourceFactory;
use ALI\Translator\MissingTranslateCatchers\CollectorMissingTranslatesCatcher;
use ALI\Translator\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\PlainTranslator\PlainTranslatorFactory;
use PHPUnit\Framework\TestCase;

class CollectorTranslateCallbackTest extends TestCase
{
    /**
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws UnsupportedLanguageAliasException
     */
    public function test()
    {
        foreach ((new SourceFactory())->iterateAllSources(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS) as $source) {
            $currentLanguageAlias = LanguagesEnum::TRANSLATION_LANGUAGE_ALIAS;
            $translator = (new PlainTranslatorFactory())->createPlainTranslator($source, $currentLanguageAlias);

            $callBack = new CollectorMissingTranslatesCatcher();
            $translator->addMissingTranslationCallback($callBack);

            $translatePhrase = $translator->translate('Test', false);
            $this->assertEquals('', $translatePhrase);

            // Add translate
            $source->saveTranslate($currentLanguageAlias, 'Cat', 'Кіт');
            $translatePhrase = $translator->translate('Cat', false);
            $source->delete('Cat');
            $this->assertEquals('Кіт', $translatePhrase);

            // Test one phrase without translate
            $originalPhraseCollection = $callBack->getOriginalPhraseCollectionsByLanguageAlias(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS);
            $this->assertEquals(['Test'], $originalPhraseCollection->getAll());
            $this->assertTrue($originalPhraseCollection->exist('Test'));
            $this->assertFalse($originalPhraseCollection->exist('Test new'));
        }
    }
}
