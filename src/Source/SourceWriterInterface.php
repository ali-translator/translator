<?php

namespace ALI\Translator\Source;

use ALI\Translator\Source\Exceptions\SourceException;

interface SourceWriterInterface
{
    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     * @throws SourceException
     */
    public function saveTranslate(string $languageAlias, string $original, string $translate);

    /**
     * @param string[] $phrases
     */
    public function saveOriginals(array $phrases);

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete(string $original);
}
