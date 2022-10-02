<?php

namespace ALI\Translator\PlainTranslator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\SourceInterface;
use ALI\Translator\TranslatorInterface;

/**
 * PlainTranslator
 * with one selected "original" language
 * and one selected "translation" language
 */
class PlainTranslator implements PlainTranslatorInterface
{
    protected string $originalLanguageAlias;
    protected string $translationLanguageAlias;
    protected TranslatorInterface $translator;

    public function __construct(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        TranslatorInterface $translator
    )
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->translationLanguageAlias = $translationLanguageAlias;
        $this->translator = $translator;
    }

    public function isCurrentLanguageOriginal(): bool
    {
        return $this->translationLanguageAlias === $this->originalLanguageAlias;
    }

    public function getTranslationLanguageAlias(): string
    {
        return $this->translationLanguageAlias;
    }

    public function getSource(): SourceInterface
    {
        return $this->translator->getSource($this->originalLanguageAlias, $this->translationLanguageAlias);
    }

    /**
     * @return callable[]
     */
    public function getMissingTranslationCallbacks(): array
    {
        return $this->translator->getMissingTranslationCatchers();
    }

    public function addMissingTranslationCallback(callable $missingTranslationCallback): void
    {
        $this->translator->addMissingTranslationCatchers($missingTranslationCallback);
    }

    public function translateAll(array $phrases): TranslatePhraseCollection
    {
        return $this->translator->translateAll($this->originalLanguageAlias, $this->translationLanguageAlias, $phrases);
    }

    public function translate(?string $phrase, bool $withTranslationFallback): ?string
    {
        $phrase = $phrase ?: '';

        return $this->translator->translate($this->originalLanguageAlias, $this->translationLanguageAlias, $phrase, $withTranslationFallback);
    }

    public function saveTranslate(string $original,string $translate, string $translationLanguageAlias = null): void
    {
        $translationLanguageAlias = $translationLanguageAlias ?: $this->translationLanguageAlias;

        $this->translator->saveTranslate($this->originalLanguageAlias, $translationLanguageAlias, $original, $translate);
    }

    public function delete(string $original): void
    {
        $this->translator->delete($this->originalLanguageAlias, $original, $this->translationLanguageAlias);
    }
}
