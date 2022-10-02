<?php

namespace ALI\Translator\MissingTranslateCatchers;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\TranslatorInterface;

class CollectorMissingTranslatesCatcher
{
    /**
     * @var OriginalPhraseCollection[]
     */
    private $originalPhraseCollections = [];

    /**
     * @param string $languageAlias
     * @param string $searchPhrase
     * @param TranslatorInterface $translator
     */
    public function __invoke(string $languageAlias, string $searchPhrase, TranslatorInterface $translator)
    {
        $this->getOriginalPhraseCollectionsByLanguageAlias($languageAlias)->add($searchPhrase);
    }

    /**
     * @return OriginalPhraseCollection[]
     */
    public function getOriginalPhraseCollections(): array
    {
        return $this->originalPhraseCollections;
    }

    /**
     * @param string $languageAlias
     * @return OriginalPhraseCollection|mixed
     */
    public function getOriginalPhraseCollectionsByLanguageAlias(string $languageAlias): OriginalPhraseCollection
    {
        if (!isset($this->originalPhraseCollections[$languageAlias])) {
            $this->originalPhraseCollections[$languageAlias] = new OriginalPhraseCollection($languageAlias);
        }

        return $this->originalPhraseCollections[$languageAlias];
    }
}
