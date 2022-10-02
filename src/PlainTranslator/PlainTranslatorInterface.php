<?php

namespace ALI\Translator\PlainTranslator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\SourceInterface;

interface PlainTranslatorInterface
{
    public function translateAll(array $phrases): TranslatePhraseCollection;

    public function translate(?string $phrase, bool $withTranslationFallback): ?string;

    public function saveTranslate(string $original, string $translate, ?string $translationLanguageAlias = null): void;

    /**
     * Delete original and all translated phrases
     */
    public function delete(string $original): void;

    public function isCurrentLanguageOriginal(): bool;

    public function getTranslationLanguageAlias(): string;

    public function getSource(): SourceInterface;

    public function addMissingTranslationCallback(callable $missingTranslationCallback): void;
}
