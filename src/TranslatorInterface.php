<?php

namespace ALI\Translator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\SourceInterface;

interface TranslatorInterface
{
    public function getSource(
        string $originalLanguageAlias,
        string $translationLanguageAlias = null
    ): ?SourceInterface;

    /**
     * @return callable[]
     */
    public function getMissingTranslationCatchers(): array;

    public function addMissingTranslationCatchers(
        callable $missingTranslationCallback
    ): void;

    public function translateAll(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        array $phrases
    ): TranslatePhraseCollection;

    public function translate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        ?string $phrase,
        bool $withTranslationFallback
    ): ?string;

    public function saveTranslate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        string $original,
        string $translate
    ): void;

    public function delete(
        string $originalLanguageAlias,
        string $original,
        string $translationLanguageAlias = null
    ): void;
}
