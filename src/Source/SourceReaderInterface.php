<?php

namespace ALI\Translator\Source;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Exceptions\SourceException;

interface SourceReaderInterface
{
    /**
     * @param string $phrase
     * @param string $languageAlias
     * @return null|string
     * @throws SourceException
     */
    public function getTranslate(string $phrase, string $languageAlias): ?string;

    /**
     * Get an array with original phrases as a key
     * and translated into a value
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     */
    public function getTranslates(array $phrases, string $languageAlias): array;

    /**
     * Get all translations of one original
     *
     * @param string $phrase
     * @param array|null $languagesAliases - leave "null" to get all languages
     * @return array
     */
    public function getAllOriginalTranslates(string $phrase, ?array $languagesAliases = null): array;

    /**
     * @param string[] $phrases
     * @return string[]
     */
    public function getExistOriginals(array $phrases): array;

    /**
     * @param string $translationLanguageAlias
     * @param int $offset
     * @param int|null $limit
     * @return OriginalPhraseCollection
     */
    public function getOriginalsWithoutTranslate(string $translationLanguageAlias, int $offset = 0, int $limit = null): OriginalPhraseCollection;
}
