<?php

namespace ALI\Translator\PlainTranslator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\SourceInterface;

/**
 * TranslatorInterface
 */
interface PlainTranslatorInterface
{
    /**
     * @param array $phrases
     * @return TranslatePhraseCollection
     */
    public function translateAll($phrases): TranslatePhraseCollection;

    /**
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return null|string
     */
    public function translate(string $phrase,$withTranslationFallback = false);

    /**
     * @param string $original
     * @param string $translate
     * @param null $translationLanguageAlias
     */
    public function saveTranslate(string $original, string $translate, $translationLanguageAlias = null);

    /**
     * Delete original and all translated phrases
     *
     * @param string $original
     */
    public function delete(string $original);

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal(): bool;

    /**
     * @return string
     */
    public function getTranslationLanguageAlias(): string;

    /**
     * @return SourceInterface
     */
    public function getSource(): SourceInterface;

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCallback(callable $missingTranslationCallback);
}
