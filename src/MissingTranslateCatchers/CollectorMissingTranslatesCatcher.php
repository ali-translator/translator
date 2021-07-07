<?php

namespace ALI\Translator\MissingTranslateCatchers;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\PlainTranslator\PlainTranslatorInterface;

/**
 * Class
 */
class CollectorMissingTranslatesCatcher
{
    /**
     * @var OriginalPhraseCollection[]
     */
    private $originalPhraseCollections = [];

    /**
     * @param string $languageAlias
     * @param string $searchPhrase
     * @param PlainTranslatorInterface $translator
     */
    public function __invoke($languageAlias, $searchPhrase, $translator)
    {
        $this->getOriginalPhraseCollectionsByLanguageAlias($languageAlias)->add($searchPhrase);
    }

    /**
     * @return OriginalPhraseCollection[]
     */
    public function getOriginalPhraseCollections()
    {
        return $this->originalPhraseCollections;
    }

    /**
     * @param string $languageAlias
     * @return OriginalPhraseCollection|mixed
     */
    public function getOriginalPhraseCollectionsByLanguageAlias($languageAlias)
    {
        if (!isset($this->originalPhraseCollections[$languageAlias])) {
            $this->originalPhraseCollections[$languageAlias] = new OriginalPhraseCollection($languageAlias);
        }

        return $this->originalPhraseCollections[$languageAlias];
    }
}
