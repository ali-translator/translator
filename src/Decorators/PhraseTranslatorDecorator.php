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

    public function translateAll($originalLanguageAlias, $translationLanguageAlias, $phrases)
    {
        $decoratedOriginalPhrases = [];
        foreach ($phrases as $phraseKey => $phrase) {
            $decoratedOriginalPhrases[$phraseKey] = $this->originalDecoratorManager->decorate($phrase);
        }
        $translatePhrasePacket = $this->translator->translateAll($originalLanguageAlias, $translationLanguageAlias, $decoratedOriginalPhrases);
        $decoratedTranslatedPhrasePacket = new TranslatePhraseCollection();
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

    public function translate($originalLanguageAlias, $translationLanguageAlias, $phrase, $withTranslationFallback = false)
    {
        $decoratedOriginalPhrase = $this->originalDecoratorManager->decorate($phrase);
        $translate = $this->translator->translate($originalLanguageAlias, $translationLanguageAlias, $decoratedOriginalPhrase, $withTranslationFallback);
        if ($translate) {
            $translate = $this->translateDecoratorManager->decorate($phrase, $translate);
        }

        return $translate;
    }

    public function saveTranslate($originalLanguageAlias, $translationLanguageAlias, $original, $translate)
    {
        $original = $this->originalDecoratorManager->decorate($original);
        $this->translator->saveTranslate($originalLanguageAlias, $translationLanguageAlias, $original, $translate);
    }

    public function delete($originalLanguageAlias, $original, $translationLanguageAlias = null)
    {
        $original = $this->originalDecoratorManager->decorate($original);
        $this->translator->delete($originalLanguageAlias, $original, $translationLanguageAlias);
    }

    public function getSource($originalLanguageAlias, $translationLanguageAlias = null)
    {
        return $this->translator->getSource($originalLanguageAlias, $translationLanguageAlias);
    }

    public function addMissingTranslationCatchers(callable $missingTranslationCallback)
    {
        $this->translator->addMissingTranslationCatchers($missingTranslationCallback);
    }

    public function getMissingTranslationCatchers()
    {
        $this->translator->getMissingTranslationCatchers();
    }
}
