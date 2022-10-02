<?php

namespace ALI\Translator\Source\Sources\FileSources\CsvSource;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\Source\Sources\FileSources\FileSourceAbstract;
use RuntimeException;

/**
 * Source for simple translation storage. Directory with text files.
 * File names - will be in format language_alias.file_extension.
 * Language alias - allowed only word symbols and "-_"
 * Content in files - first original and after delimiter - translate.
 * Class FileSource
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
     * @return null|string
     * @throws FileReadPermissionsException
     * @throws DirectoryNotFoundException
     * @throws UnsupportedLanguageAliasException
     */
    public function getTranslate(string $phrase, string $languageAliasAlias)
    {
        $this->preloadTranslates($languageAliasAlias);

        if (!empty($this->allTranslates[$languageAliasAlias][$phrase])) {
            return $this->allTranslates[$languageAliasAlias][$phrase];
        }

        return null;
    }

    /**
     * @param $languageAlias
     * @param bool $forceLoading
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
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
        foreach ($this->iterateTranslationFileData($languageAlias) as $languageFileData) {
            $translates[$languageFileData[0]] = isset($languageFileData[1]) ? $languageFileData[1] : '';
        }

        return $translates;
    }

    const INDEXED_BY_ID = 'id';
    const INDEXED_BY_ORIGINAL_CONTENT = 'original';

    public function parseOriginalsLanguageRowsIds($indexedBy = self::INDEXED_BY_ID)
    {
        $translates = [];
        foreach ($this->iterateTranslationFileData($this->originalLanguageAlias) as $languageFileData) {
            switch ($indexedBy) {
                case self::INDEXED_BY_ID:
                    $key = $languageFileData[2];
                    $value = $languageFileData[0];
                    break;
                case self::INDEXED_BY_ORIGINAL_CONTENT:
                    $key = $languageFileData[0];
                    $value = $languageFileData[2];
                    break;
                default:
                    throw new RuntimeException('Undefined "indexBy" type');
                    break;
            }
            $translates[$key] = $value;
        }

        return $translates;
    }

    /**
     * @param string $languageAlias
     * @throws UnsupportedLanguageAliasException
     */
    protected function saveLanguageFile($languageAlias)
    {
        $translatesData = $this->allTranslates[$languageAlias];
        $filePath = $this->getLanguageFilePath($languageAlias);
        $fileResource = fopen($filePath, 'wb');

        foreach ($translatesData as $original => $translate) {
            $id = $this->getNextIncrementId();
            fputcsv($fileResource, [$original, $translate, $id], $this->delimiter);
        }

        fclose($fileResource);
    }

    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function saveTranslate(string $languageAlias, string $original, string $translate)
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
    public function delete(string $original)
    {
        $dataFiles = glob($this->getDirectoryPath() . DIRECTORY_SEPARATOR . '*.' . $this->filesExtension);
        foreach ($dataFiles as $file) {
            $fileInfo = pathinfo($file);
            $languageAlias = explode('_', $fileInfo['filename'])[1];
            $this->allTranslates[$languageAlias] = $this->parseLanguageFile($languageAlias);
            if (array_key_exists($original, $this->allTranslates[$languageAlias])) {
                unset($this->allTranslates[$languageAlias][$original]);
                $this->saveLanguageFile($languageAlias);
            }
        }
    }

    /**
     * @param array $phrases
     * @throws DirectoryNotFoundException
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
     * @param array $phrases
     * @return array|string[]
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function getExistOriginals(array $phrases): array
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
     * @param string[] $phrases
     * @return string[]
     */
    public function getOriginalsIds(array $phrases): array
    {
        $originalContentWithId = $this->parseOriginalsLanguageRowsIds(self::INDEXED_BY_ORIGINAL_CONTENT);

        $result = [];
        foreach ($phrases as $phrase) {
            if (isset($originalContentWithId[$phrase])) {
                $result[$phrase] = $originalContentWithId[$phrase];
            }
        }

        return $result;
    }

    /**
     * @param string[] $originalsIds
     * @return string[]
     */
    public function getOriginalsByIds(array $originalsIds): array
    {
        $idWithOriginalContent = $this->parseOriginalsLanguageRowsIds(self::INDEXED_BY_ID);

        $result = [];
        foreach ($originalsIds as $searchedId) {
            if (isset($idWithOriginalContent[$searchedId])) {
                $result[$searchedId] = $idWithOriginalContent[$searchedId];
                $result[$searchedId] = $idWithOriginalContent[$searchedId];
            }
        }

        return $result;
    }

    /**
     * @param string $translationLanguageAlias
     * @param int $offset
     * @param null $limit
     * @return OriginalPhraseCollection
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function getOriginalsWithoutTranslate(string $translationLanguageAlias, $offset = 0, $limit = null): OriginalPhraseCollection
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

    /**
     * @param $languageAlias
     * @return \Generator
     */
    protected function iterateTranslationFileData($languageAlias)
    {
        if (!file_exists($this->getDirectoryPath()) || !is_dir($this->getDirectoryPath())) {
            throw new DirectoryNotFoundException('Directory not found ' . $this->getDirectoryPath());
        }

        $languageFile = $this->getLanguageFilePath($languageAlias);

        if (file_exists($languageFile)) {
            if (!is_readable($languageFile)) {
                throw new FileReadPermissionsException('Cannot read file ' . $languageFile);
            }

            $fileResource = fopen($languageFile, 'rb');
            while (($data = fgetcsv($fileResource, 0, $this->delimiter)) !== false) {
                yield $data;
            }
            fclose($fileResource);
        }
    }
}
