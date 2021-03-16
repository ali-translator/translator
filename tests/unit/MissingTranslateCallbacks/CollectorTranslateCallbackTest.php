<?php

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use ALI\Translator\Tests\components\Factories\SourceFactory;
use ALI\Translator\MissingTranslateCatchers\CollectorMissingTranslatesCatcher;
use ALI\Translator\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\PlainTranslator\PlainTranslatorFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
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

            $originalPhraseCollection = new OriginalPhraseCollection(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS);
            $callBack = new CollectorMissingTranslatesCatcher($originalPhraseCollection);

            $translator->addMissingTranslationCallback($callBack);

            $translatePhrase = $translator->translate('Test');
            $this->assertEquals('', $translatePhrase);

            // Add translate
            $source->saveTranslate($currentLanguageAlias, 'Cat', 'Кіт');
            $translatePhrase = $translator->translate('Cat');
            $source->delete('Cat');
            $this->assertEquals('Кіт', $translatePhrase);

            // Test one phrase without translate
            $this->assertEquals(['Test'], $callBack->getOriginalPhraseCollection()->getAll());
            $this->assertTrue($callBack->getOriginalPhraseCollection()->exist('Test'));
            $this->assertFalse($callBack->getOriginalPhraseCollection()->exist('Test new'));
        }
    }
}
