<?php

namespace ALI\Translator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\SourceInterface;

/**
 * Interface
 */
interface TranslatorInterface
{
    /**
     * @param $originalLanguageAlias
     * @param $translationLanguageAlias
     * @return SourceInterface|null
     * @throws \Exception
     */
    public function getSource($originalLanguageAlias, $translationLanguageAlias = null);

    /**
     * @return callable[]
     */
    public function getMissingTranslationCatchers();

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCatchers(callable $missingTranslationCallback);

    /**
     * @param $originalLanguageAlias
     * @param $translationLanguageAlias
     * @param $phrases
     * @return TranslatePhraseCollection
     */
    public function translateAll($originalLanguageAlias, $translationLanguageAlias, $phrases);

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string|null
     */
    public function translate(
        $originalLanguageAlias,
        $translationLanguageAlias,
        $phrase,
        $withTranslationFallback = false
    );

    /**
     * @param $original
     * @param $translate
     * @param string $languageAlias
     */
    public function saveTranslate(
        $originalLanguageAlias,
        $translationLanguageAlias,
        $original,
        $translate
    );

    /**
     * @param string $originalLanguageAlias
     * @param string $original
     * @param null|string $translationLanguageAlias
     */
    public function delete(
        $originalLanguageAlias,
        $original,
        $translationLanguageAlias = null
    );
}
