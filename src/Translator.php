<?php

namespace ALI\Translator;

use ALI\Translator\PhraseCollection\TranslatePhraseCollection;
use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\SourceInterface;
use ALI\Translator\Source\SourcesCollection;
use RuntimeException;

/**
 * Translator
 *
 * Class allow translation from different original languages,
 * to different translation languages
 */
class Translator implements TranslatorInterface
{
    protected SourcesCollection $sourceCollection;

    /**
     * @var callable[]
     */
    protected array $missingTranslationCallbacks = [];

    public function __construct(SourcesCollection $sourceCollection)
    {
        $this->sourceCollection = $sourceCollection;
    }

    public function getSourceCollection(): SourcesCollection
    {
        return $this->sourceCollection;
    }

    public function getSource(string $originalLanguageAlias,string $translationLanguageAlias = null): SourceInterface
    {
        $source = $this->sourceCollection->getSource($originalLanguageAlias, $translationLanguageAlias);
        if (!$source) {
            throw new RuntimeException('Not found source for ' . $originalLanguageAlias . '->' . $translationLanguageAlias . ' language pair');
        }

        return $source;
    }

    /**
     * @return callable[]
     */
    public function getMissingTranslationCatchers(): array
    {
        return $this->missingTranslationCallbacks;
    }

    public function addMissingTranslationCatchers(callable $missingTranslationCallback): void
    {
        $this->missingTranslationCallbacks[] = $missingTranslationCallback;
    }

    public function translateAll(string $originalLanguageAlias, string $translationLanguageAlias, array $phrases): TranslatePhraseCollection
    {
        $translatePhrasePacket = new TranslatePhraseCollection($originalLanguageAlias, $translationLanguageAlias);
        if ($originalLanguageAlias === $translationLanguageAlias) {
            foreach ($phrases as $phrase) {
                $translatePhrasePacket->addTranslate($phrase, null);
            }

            return $translatePhrasePacket;
        }

        $source = $this->getSource($originalLanguageAlias, $translationLanguageAlias);

        $searchPhrases = array_combine($phrases, $phrases);

        $translatesFromSource = $source->getTranslates(
            $searchPhrases,
            $translationLanguageAlias
        );

        foreach ($searchPhrases as $originalPhrase => $searchPhrase) {
            $translate = $translatesFromSource[$searchPhrase] ?? null;
            if (!$translate && $searchPhrase) {
                foreach ($this->getMissingTranslationCatchers() as $missingTranslationCallbacks) {
                    if (is_callable($missingTranslationCallbacks)) {
                        $translate = $missingTranslationCallbacks($originalLanguageAlias, $searchPhrase, $this) ?: null;
                        if ($translate) {
                            break;
                        }
                    }
                }
            }

            $translatePhrasePacket->addTranslate($originalPhrase, $translate);
        }

        return $translatePhrasePacket;
    }

    public function translate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        ?string $phrase,
        bool $withTranslationFallback
    ): ?string
    {
        $phrase = $phrase ?: '';
        if ($originalLanguageAlias === $translationLanguageAlias) {
            return $phrase;
        }

        $translatePhraseCollection = $this->translateAll($originalLanguageAlias, $translationLanguageAlias, [$phrase]);

        return $translatePhraseCollection->getTranslate($phrase, $withTranslationFallback);
    }

    public function saveTranslate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        string $original,
        string $translate
    ): void
    {
        $source = $this->getSource($originalLanguageAlias, $translationLanguageAlias);
        $source->saveTranslate(
            $translationLanguageAlias,
            $original,
            $translate
        );
    }

    public function delete(
        string $originalLanguageAlias,
        string $original,
        string $translationLanguageAlias = null
    ): void
    {
        $this->getSource($originalLanguageAlias, $translationLanguageAlias)->delete($original);
    }
}
