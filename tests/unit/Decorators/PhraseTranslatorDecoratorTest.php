<?php

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

/**
 * Class
 */
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
        $expectedTranslatePhrase = '???????????? 123 ?????? 000';
        $comparableOriginalPhrase = 'Hello 321 Hi 643';
        $comparableExpectedOriginalPhrase = '???????????? 321 ?????? 643';

        $translatedPhrase = $plainPhraseTranslatorDecorator->translate($originalPhrase);
        $this->assertNull($translatedPhrase);

        $plainPhraseTranslatorDecorator->saveTranslate($originalPhrase, $expectedTranslatePhrase);
        $translatedPhrase = $plainPhraseTranslatorDecorator->translate($originalPhrase);
        $this->assertEquals($expectedTranslatePhrase, $translatedPhrase);
        $translatedPhrase = $plainPhraseTranslatorDecorator->translate($comparableOriginalPhrase);
        $this->assertEquals($comparableExpectedOriginalPhrase, $translatedPhrase);

        $translatedPhrase = $plainPhraseTranslatorDecorator->translateAll([
            'Hello 12345 Hi 111',
            'Hello 111 Hi 22222',
        ]);
        $this->assertEquals([
            '???????????? 12345 ?????? 111',
            '???????????? 111 ?????? 22222',
        ], array_values($translatedPhrase->getAll()));
    }
}
