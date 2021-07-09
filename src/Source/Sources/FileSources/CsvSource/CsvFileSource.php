<?php

namespace ALI\Translator\Source\Sources\FileSources\CsvSource;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\Source\Sources\FileSources\FileSourceAbstract;

/**
 * Source for simple translation storage. Directory with text files.
 * File names - will be in format language_alias.file_extension.
 * Language alias - allowed only word symbols and "-_"
 * Content in files - first original and after delimiter - translate.
 * Class FileSource
 * @package ALI\Translator\Sources
 */
class CsvFileSource extends FileSourceAbstract
{
    /**
     * CSV delimiter - only one symbol
     * @var string
     */
    protected $delimiter;

    /**
     * @var string[][]
     */
    protected $allTranslates = [];

    /**
     * FileSource constructor.
     * @param string $directoryPath - Directory with source files
     * @param string $originalLanguageAlias
     * @param string $delimiter - CSV delimiter may be only one symbol
     * @param string $filesExtension
     */
    public function __construct($directoryPath, $originalLanguageAlias, $delimiter = ',', $filesExtension = 'csv')
    {
        $this->directoryPath = rtrim($directoryPath, '/\\');
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->delimiter = $delimiter;
        $this->filesExtension = $filesExtension;
    }

    /**
     * @param string $phrase
     * @param string $languageAliasAlias
     * @return string
     * @throws FileReadPermissionsException
     * @throws DirectoryNotFoundException
     * @throws UnsupportedLanguageAliasException
     */
    public function getTranslate($phrase, $languageAliasAlias)
    {
        $this->preloadTranslates($languageAliasAlias);

        if (!empty($this->allTranslates[$languageAliasAlias][$phrase])) {
            return $this->allTranslates[$languageAliasAlias][$phrase];
        }

        return null;
    }

    protected function preloadTranslates($languageAlias, $forceLoading = false)
    {
        if (!isset($this->allTranslates[$languageAlias]) || $forceLoading) {
            $this->allTranslates[$languageAlias] = $this->parseLanguageFile($languageAlias);
        }
    }

    /**
     * @param string $languageAlias
     * @return array
     * @throws FileReadPermissionsException
     * @throws DirectoryNotFoundException
     * @throws UnsupportedLanguageAliasException
     */
    protected function parseLanguageFile($languageAlias)
    {
        $translates = [];

        if (!file_exists($this->getDirectoryPath()) || !is_dir($this->getDirectoryPath())) {
            throw new DirectoryNotFoundException('Directory not found ' . $this->getDirectoryPath());
        }

        $languageFile = $this->getLanguageFilePath($languageAlias);

        if (file_exists($languageFile)) {
            if (!is_readable($languageFile)) {
                throw new FileReadPermissionsException('Cannot read file ' . $languageFile);
            }

            $fileResource = fopen($languageFile, 'r');
            while (($data = fgetcsv($fileResource, 0, $this->delimiter)) !== false) {
                $translates[$data[0]] = isset($data[1]) ? $data[1] : '';
            }
            fclose($fileResource);
        }

        return $translates;
    }

    /**
     * @param string $languageAlias
     * @param array $translatesData - [original => translate]
     * @throws UnsupportedLanguageAliasException
     * @throws FileNotWritableException
     */
    protected function saveLanguageFile($languageAlias)
    {
        $translatesData = $this->allTranslates[$languageAlias];
        $filePath = $this->getLanguageFilePath($languageAlias);
        $fileResource = fopen($filePath, 'w');

        foreach ($translatesData as $original => $translate) {
            fputcsv($fileResource, [$original, $translate], $this->delimiter);
        }

        fclose($fileResource);
    }

    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     * @throws FileNotWritableException
     */
    public function saveTranslate($languageAlias, $original, $translate)
    {
        $this->saveOriginals([$original]);

        // Save to translate
        $this->preloadTranslates($languageAlias);
        if (!isset($this->allTranslates[$languageAlias][$original]) || $this->allTranslates[$languageAlias][$original] !== $translate) {
            $this->allTranslates[$languageAlias][$original] = $translate;
            $this->saveLanguageFile($languageAlias);
        }
    }

    /**
     * Delete original and all translated phrases
     * @param string $original
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function delete($original)
    {
        $dataFiles = glob($this->getDirectoryPath() . DIRECTORY_SEPARATOR . '*.' . $this->filesExtension);
        foreach ($dataFiles as $file) {
            $fileInfo = pathinfo($file);
            $languageAlias = explode('_', $fileInfo['filename'])[1];
            $this->allTranslates[$languageAlias] = $this->parseLanguageFile($languageAlias);
            if (key_exists($original, $this->allTranslates[$languageAlias])) {
                unset($this->allTranslates[$languageAlias][$original]);
                $this->saveLanguageFile($languageAlias);
            }
        }
    }

    /**
     * @param array $phrases
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function saveOriginals(array $phrases)
    {
        $phrases = array_diff($phrases, $this->getExistOriginals($phrases));

        $needSaving = false;
        foreach ($phrases as $phrase) {
            if (!isset($this->allTranslates[$this->originalLanguageAlias][$phrase])) {
                $this->allTranslates[$this->originalLanguageAlias][$phrase] = $phrase;
                $needSaving = true;
            }
        }
        if ($needSaving) {
            $this->saveLanguageFile($this->originalLanguageAlias);
        }
    }

    /**
     * @inheritDoc
     */
    public function getExistOriginals(array $phrases)
    {
        $this->preloadTranslates($this->originalLanguageAlias);

        $existPhrases = [];
        foreach ($phrases as $phrase) {
            if (isset($this->allTranslates[$this->originalLanguageAlias][$phrase])) {
                $existPhrases[] = $phrase;
            }
        }

        return $existPhrases;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalsWithoutTranslate($translationLanguageAlias, $offset = 0, $limit = null)
    {
        $this->preloadTranslates($translationLanguageAlias);

        $originalsWithoutTranslationCollection = new OriginalPhraseCollection($this->originalLanguageAlias);

        $currentOffset = 0;
        foreach ($this->allTranslates[$this->originalLanguageAlias] as $originalPhrase) {
            if ($limit && $originalsWithoutTranslationCollection->count() >= $limit) {
                return $originalsWithoutTranslationCollection;
            }
            if (empty($this->allTranslates[$translationLanguageAlias][$originalPhrase])) {
                if ($offset <= $currentOffset) {
                    $originalsWithoutTranslationCollection->add($originalPhrase);
                }
                $currentOffset++;
            }
        }

        return $originalsWithoutTranslationCollection;
    }
}
