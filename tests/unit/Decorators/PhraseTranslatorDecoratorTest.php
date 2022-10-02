<?php

namespace ALI\Translator\Tests\unit\Decorators;

use ALI\Translator\Decorators\PhraseDecorators\OriginalDecorators\ReplaceNumbersOriginalDecorator;
use ALI\Translator\Decorators\PhraseDecorators\OriginalPhraseDecoratorManager;
use ALI\Translator\Decorators\PhraseDecorators\TranslateDecorators\RestoreNumbersTranslateDecorator;
use ALI\Translator\Decorators\PhraseDecorators\TranslatePhraseDecoratorManager;
use ALI\Translator\Decorators\PhraseTranslatorDecorator;
use ALI\Translator\PlainTranslator\PlainTranslator;
use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use ALI\Translator\Tests\components\TranslatorTester;
use ALI\Translator\Translator;
use PHPUnit\Framework\TestCase;

class PhraseTranslatorDecoratorTest extends TestCase
{
    public function testBaseProxyProcess()
    {
        $translatorTester = new TranslatorTester();
        $baseTranslator = new Translator($translatorTester->generateSourceCollection());
        $phraseTranslatorDecorator = new PhraseTranslatorDecorator($baseTranslator);

        $translatorTester->test($phraseTranslatorDecorator, $this);
    }

    public function testNumberReplacingPhraseDecorators()
    {
        $translatorTester = new TranslatorTester();
        $translator = new Translator($translatorTester->generateSourceCollection());

        $originalDecoratorManger = new OriginalPhraseDecoratorManager([
            new ReplaceNumbersOriginalDecorator(),
        ]);
        $translateDecoratorManager = new TranslatePhraseDecoratorManager([
            new RestoreNumbersTranslateDecorator(),
        ]);

        $phraseTranslatorDecorator = new PhraseTranslatorDecorator($translator, $originalDecoratorManger, $translateDecoratorManager);
        $translationLanguageAlias = LanguagesEnum::TRANSLATION_LANGUAGE_ALIAS;
        $originalLanguageAlias = LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS;
        $plainPhraseTranslatorDecorator = new PlainTranslator($originalLanguageAlias, $translationLanguageAlias, $phraseTranslatorDecorator);

        $originalPhrase = 'Hello 123 Hi 000';
        $expectedTranslatePhrase = 'Привіт 123 Хай 000';
        $comparableOriginalPhrase = 'Hello 321 Hi 643';
        $comparableExpectedOriginalPhrase = 'Привіт 321 Хай 643';

        $translatedPhrase = $plainPhraseTranslatorDecorator->translate($originalPhrase, false);
        $this->assertNull($translatedPhrase);

        $plainPhraseTranslatorDecorator->saveTranslate($originalPhrase, $expectedTranslatePhrase);
        $translatedPhrase = $plainPhraseTranslatorDecorator->translate($originalPhrase, false);
        $this->assertEquals($expectedTranslatePhrase, $translatedPhrase);
        $translatedPhrase = $plainPhraseTranslatorDecorator->translate($comparableOriginalPhrase, false);
        $this->assertEquals($comparableExpectedOriginalPhrase, $translatedPhrase);

        $translatedPhrase = $plainPhraseTranslatorDecorator->translateAll([
            'Hello 12345 Hi 111',
            'Hello 111 Hi 22222',
        ]);
        $this->assertEquals([
            'Привіт 12345 Хай 111',
            'Привіт 111 Хай 22222',
        ], array_values($translatedPhrase->getAll()));
    }
}
