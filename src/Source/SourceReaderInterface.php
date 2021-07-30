<?php

namespace ALI\Translator\Source;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Exceptions\SourceException;

interface SourceReaderInterface
{
    /**
     * @param string $phrase
     * @param string $languageAliasAlias
     * @return null|string
     * @throws SourceException
     */
    public function getTranslate(string $phrase, string $languageAliasAlias);

    /**
     * Get an array with original phrases as a key
     * and translated into a value
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     */
    public function getTranslates(array $phrases, string $languageAlias): array;

    /**
     * @param string[] $phrases
     * @return string[]
     */
    public function getExistOriginals(array $phrases): array;

    /**
     * @param string $translationLanguageAlias
     * @param int $offset
     * @param null|int $limit
     * @return OriginalPhraseCollection
     */
    public function getOriginalsWithoutTranslate(string $translationLanguageAlias, $offset = 0, $limit = null): OriginalPhraseCollection;
}
