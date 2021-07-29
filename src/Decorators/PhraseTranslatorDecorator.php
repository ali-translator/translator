<?php

namespace ALI\Translator\Decorators;

use ALI\Translator\Decorators\PhraseDecorators\OriginalPhraseDecoratorManager;
use ALI\Translator\Decorators\PhraseDecorators\TranslatePhraseDecoratorManager;
use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\TranslatorInterface;

/**
 * Decorate original and translated phrases in conjunction with `Translator` class
 */
class PhraseTranslatorDecorator implements TranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var OriginalPhraseDecoratorManager
     */
    protected $originalDecoratorManager;

    /**
     * @var TranslatePhraseDecoratorManager
     */
    protected $translateDecoratorManager;

    /**
     * @param TranslatorInterface $translator
     * @param OriginalPhraseDecoratorManager $originalDecoratorManager
     * @param TranslatePhraseDecoratorManager $translateDecoratorManager
     */
    public function __construct(
        TranslatorInterface $translator,
        OriginalPhraseDecoratorManager $originalDecoratorManager = null,
        TranslatePhraseDecoratorManager $translateDecoratorManager = null
    )
    {
        $this->translator = $translator;
        $this->originalDecoratorManager = $originalDecoratorManager ?: new OriginalPhraseDecoratorManager();
        $this->translateDecoratorManager = $translateDecoratorManager ?: new TranslatePhraseDecoratorManager();
    }

    public function translateAll(string $originalLanguageAlias,string $translationLanguageAlias,array $phrases): TranslatePhraseCollection
    {
        $decoratedOriginalPhrases = [];
        foreach ($phrases as $phraseKey => $phrase) {
            $decoratedOriginalPhrases[$phraseKey] = $this->originalDecoratorManager->decorate($phrase);
        }
        $translatePhrasePacket = $this->translator->translateAll($originalLanguageAlias, $translationLanguageAlias, $decoratedOriginalPhrases);
        $decoratedTranslatedPhrasePacket = new TranslatePhraseCollection($originalLanguageAlias, $translationLanguageAlias);
        foreach ($decoratedOriginalPhrases as $key => $originalDecoratedPhrase) {
            $originalPhrase = $phrases[$key];
            $translatePhrase =$translatePhrasePacket->getTranslate($originalDecoratedPhrase);
            if ($translatePhrase) {
                $translatePhrase = $this->translateDecoratorManager->decorate($originalPhrase, $translatePhrase);
            }
            $decoratedTranslatedPhrasePacket->addTranslate($originalPhrase, $translatePhrase);
        }

        return $decoratedTranslatedPhrasePacket;
    }

    public function translate(string $originalLanguageAlias, string $translationLanguageAlias, string $phrase, bool $withTranslationFallback = false)
    {
        $decoratedOriginalPhrase = $this->originalDecoratorManager->decorate($phrase);
        $translate = $this->translator->translate($originalLanguageAlias, $translationLanguageAlias, $decoratedOriginalPhrase, $withTranslationFallback);
        if ($translate) {
            $translate = $this->translateDecoratorManager->decorate($phrase, $translate);
        }

        return $translate;
    }

    public function saveTranslate(string $originalLanguageAlias,string $translationLanguageAlias,string $original,string $translate)
    {
        $original = $this->originalDecoratorManager->decorate($original);
        $this->translator->saveTranslate($originalLanguageAlias, $translationLanguageAlias, $original, $translate);
    }

    public function delete(string $originalLanguageAlias, string $original, string $translationLanguageAlias = null)
    {
        $original = $this->originalDecoratorManager->decorate($original);
        $this->translator->delete($originalLanguageAlias, $original, $translationLanguageAlias);
    }

    public function getSource(string $originalLanguageAlias,string $translationLanguageAlias = null)
    {
        return $this->translator->getSource($originalLanguageAlias, $translationLanguageAlias);
    }

    public function addMissingTranslationCatchers(callable $missingTranslationCallback)
    {
        $this->translator->addMissingTranslationCatchers($missingTranslationCallback);
    }

    public function getMissingTranslationCatchers(): array
    {
        return $this->translator->getMissingTranslationCatchers();
    }
}
