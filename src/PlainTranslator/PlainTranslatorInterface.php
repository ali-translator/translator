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
    public function translateAll($phrases);

    /**
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string
     */
    public function translate($phrase, $withTranslationFallback = false);

    /**
     * @param string $original
     * @param string $translate
     * @param null $translationLanguageAlias
     */
    public function saveTranslate($original, $translate, $translationLanguageAlias = null);

    /**
     * Delete original and all translated phrases
     *
     * @param string $original
     */
    public function delete($original);

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal();

    /**
     * @return string
     */
    public function getTranslationLanguageAlias();

    /**
     * @return SourceInterface
     */
    public function getSource();

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCallback(callable $missingTranslationCallback);
}
