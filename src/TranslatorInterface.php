<?php

namespace ALI\Translator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\SourceInterface;
use RuntimeException;

/**
 * Interface
 */
interface TranslatorInterface
{
    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @return SourceInterface|null
     * @throws RuntimeException
     */
    public function getSource(string $originalLanguageAlias, string $translationLanguageAlias = null);

    /**
     * @return callable[]
     */
    public function getMissingTranslationCatchers(): array;

    /**
     * @param callable $missingTranslationCallback
     * @return void
     */
    public function addMissingTranslationCatchers(callable $missingTranslationCallback);

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param array $phrases
     * @return TranslatePhraseCollection
     */
    public function translateAll(string $originalLanguageAlias, string $translationLanguageAlias, array $phrases): TranslatePhraseCollection;

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string|null
     */
    public function translate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        string $phrase,
        bool $withTranslationFallback = false
    );

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param string $original
     * @param string $translate
     * @return void
     */
    public function saveTranslate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        string $original,
        string $translate
    );

    /**
     * @param string $originalLanguageAlias
     * @param string $original
     * @param null|string $translationLanguageAlias
     * @return void
     */
    public function delete(
        string $originalLanguageAlias,
        string $original,
        string $translationLanguageAlias = null
    );
}
