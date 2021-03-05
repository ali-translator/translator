<?php

namespace ALI\Translator\PlainTranslator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\SourceInterface;
use ALI\Translator\TranslatorInterface;

/**
 * PlainTranslator
 * with one selected "original" language
 * and one selected "translation" language
 */
class PlainTranslator implements PlainTranslatorInterface
{
    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var string
     */
    protected $translationLanguageAlias;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Translate constructor
     *
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param TranslatorInterface $translator
     */
    public function __construct(
        $originalLanguageAlias,
        $translationLanguageAlias,
        TranslatorInterface $translator
    )
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->translationLanguageAlias = $translationLanguageAlias;
        $this->translator = $translator;
    }

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->translationLanguageAlias === $this->originalLanguageAlias;
    }

    /**
     * @return string
     */
    public function getTranslationLanguageAlias()
    {
        return $this->translationLanguageAlias;
    }

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->translator->getSource($this->originalLanguageAlias, $this->translationLanguageAlias);
    }

    /**
     * @return callable[]
     */
    public function getMissingTranslationCallbacks()
    {
        return $this->translator->getMissingTranslationCatchers();
    }

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCallback(callable $missingTranslationCallback)
    {
        $this->translator->addMissingTranslationCatchers($missingTranslationCallback);
    }

    /**
     * @param array $phrases
     * @return TranslatePhraseCollection
     */
    public function translateAll($phrases)
    {
        return $this->translator->translateAll($this->originalLanguageAlias, $this->translationLanguageAlias, $phrases);
    }

    /**
     * Fast translate without buffers and processors
     *
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string|null
     */
    public function translate($phrase, $withTranslationFallback = false)
    {
        return $this->translator->translate($this->originalLanguageAlias, $this->translationLanguageAlias, $phrase, $withTranslationFallback);
    }

    /**
     * @param $original
     * @param $translate
     * @param string $translationLanguageAlias
     * @throws SourceException
     */
    public function saveTranslate($original, $translate, $translationLanguageAlias = null)
    {
        $translationLanguageAlias = $translationLanguageAlias ?: $this->translationLanguageAlias;

        $this->translator->saveTranslate($this->originalLanguageAlias, $translationLanguageAlias, $original, $translate);
    }

    /**
     * Delete original and all translated phrases
     * @param $original
     */
    public function delete($original)
    {
        $this->translator->delete($this->originalLanguageAlias, $original, $this->translationLanguageAlias);
    }
}
