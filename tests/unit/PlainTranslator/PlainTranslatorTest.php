<?php

use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use ALI\Translator\Tests\components\Factories\SourceFactory;
use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\SourceInterface;
use ALI\Translator\PlainTranslator\PlainTranslatorFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class PlainTranslatorTest extends TestCase
{
    /**
     * @throws SourceException
     */
    public function testTranslationFallback()
    {
        foreach ((new SourceFactory())->iterateAllSources(LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS) as $source) {
            $originalPhrase = 'Some test phrase';
            $translatedPhrase = 'Деяка тестова фраза';

            $this->checkTranslationWithoutFallback($source, $originalPhrase, $translatedPhrase);
            $this->checkTranslationFallback($source, $originalPhrase, $translatedPhrase);
        }
    }

    /**
     * @param SourceInterface $source
     * @param $originalPhrase
     * @param $translatedPhrase
     * @throws SourceException
     */
    private function checkTranslationWithoutFallback(SourceInterface $source, $originalPhrase, $translatedPhrase)
    {
        $translator = (new PlainTranslatorFactory())->createPlainTranslator($source, LanguagesEnum::TRANSLATION_LANGUAGE_ALIAS);

        $this->assertEquals($translator->translate($originalPhrase), '');
        $translator->saveTranslate($originalPhrase, $translatedPhrase);
        $this->assertEquals($translator->translate($originalPhrase), $translatedPhrase);
        $translator->delete($originalPhrase);
    }

    /**
     * @param SourceInterface $source
     * @param $originalPhrase
     * @param $translatedPhrase
     * @throws SourceException
     */
    private function checkTranslationFallback(SourceInterface $source, $originalPhrase, $translatedPhrase)
    {
        $translator = (new PlainTranslatorFactory())->createPlainTranslator($source, LanguagesEnum::TRANSLATION_LANGUAGE_ALIAS,);

        $this->assertEquals($originalPhrase, $translator->translate($originalPhrase, true));
        $translator->saveTranslate($originalPhrase, $translatedPhrase);
        $this->assertEquals($translatedPhrase, $translator->translate($originalPhrase, true));
        $translator->delete($originalPhrase);
    }
}
