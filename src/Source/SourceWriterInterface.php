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
    public function saveTranslate(string $languageAlias, string $original, string $translate): void;

    /**
     * @param string[] $phrases
     */
    public function saveOriginals(array $phrases): void;

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete(string $original): void;
}
