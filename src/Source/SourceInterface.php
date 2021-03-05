<?php

namespace ALI\Translator\Source;

use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\Installers\SourceInstallerInterface;

/**
 * SourceInterface Interface
 */
interface SourceInterface
{
    /**
     * @return string
     */
    public function getOriginalLanguageAlias();

    /**
     * @param string $phrase
     * @param string $languageAliasAlias
     * @return string
     * @throws SourceException
     */
    public function getTranslate($phrase, $languageAliasAlias);

    /**
     * Get an array with original phrases as a key
     * and translated into a value
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     */
    public function getTranslates(array $phrases, $languageAlias);

    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     * @throws SourceException
     */
    public function saveTranslate($languageAlias, $original, $translate);

    /**
     * @param string[] $phrases
     */
    public function saveOriginals(array $phrases);

    /**
     * @param string[] $phrases
     * @return string[]
     */
    public function getExistOriginals(array $phrases);

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete($original);

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount();

    /**
     * @return SourceInstallerInterface
     */
    public function generateInstaller();
}
