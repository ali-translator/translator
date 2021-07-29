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
    /**
     * @var SourcesCollection
     */
    protected $sourceCollection;

    /**
     * @var callable[]
     */
    protected $missingTranslationCallbacks = [];

    /**
     * @param SourcesCollection $sourceCollection
     */
    public function __construct(SourcesCollection $sourceCollection)
    {
        $this->sourceCollection = $sourceCollection;
    }

    /**
     * @return SourcesCollection
     */
    public function getSourceCollection(): SourcesCollection
    {
        return $this->sourceCollection;
    }

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @return SourceInterface
     * @throws RuntimeException
     */
    public function getSource($originalLanguageAlias, $translationLanguageAlias = null): SourceInterface
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

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCatchers(callable $missingTranslationCallback)
    {
        $this->missingTranslationCallbacks[] = $missingTranslationCallback;
    }

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param array $phrases
     * @return TranslatePhraseCollection
     * @throws \Exception
     */
    public function translateAll(string $originalLanguageAlias,string $translationLanguageAlias,array $phrases): TranslatePhraseCollection
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
            if (!$translate) {
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

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string|null
     * @throws \Exception
     */
    public function translate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        string $phrase,
        bool $withTranslationFallback = false
    )
    {
        if ($originalLanguageAlias === $translationLanguageAlias) {
            return $phrase;
        }

        $translatePhraseCollection = $this->translateAll($originalLanguageAlias, $translationLanguageAlias, [$phrase]);

        return $translatePhraseCollection->getTranslate($phrase, $withTranslationFallback);
    }

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param string $original
     * @param string $translate
     * @throws SourceException
     */
    public function saveTranslate(
        string $originalLanguageAlias,
        string $translationLanguageAlias,
        string $original,
        string $translate
    )
    {
        $source = $this->getSource($originalLanguageAlias, $translationLanguageAlias);
        $source->saveTranslate(
            $translationLanguageAlias,
            $original,
            $translate
        );
    }

    /**
     * @param string $originalLanguageAlias
     * @param string $original
     * @param null|string $translationLanguageAlias
     * @throws \Exception
     */
    public function delete(
        string $originalLanguageAlias,
        string $original,
        string $translationLanguageAlias = null
    )
    {
        $this->getSource($originalLanguageAlias, $translationLanguageAlias)->delete($original);
    }
}
