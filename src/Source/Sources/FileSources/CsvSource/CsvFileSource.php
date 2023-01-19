<?php

namespace ALI\Translator\Source\Sources\FileSources\CsvSource;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translator\Source\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\Source\Sources\FileSources\FileSourceAbstract;
use Generator;
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
     */
    protected string $delimiter;

    /**
     * @var string[][]
     */
    protected array $allTranslates = [];

    /**
     * FileSource constructor.
     * @param string $directoryPath - Directory with source files
     * @param string $originalLanguageAlias
     * @param string $delimiter - CSV delimiter may be only one symbol
     * @param string $filesExtension
     */
    public function __construct(string $directoryPath, string $originalLanguageAlias, string $delimiter = ',', string $filesExtension = 'csv')
    {
        $this->directoryPath = rtrim($directoryPath, '/\\');
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->delimiter = $delimiter;
        $this->filesExtension = $filesExtension;
    }

    public function getTranslate(string $phrase, string $languageAlias): ?string
    {
        $this->preloadTranslates($languageAlias);

        if (!empty($this->allTranslates[$languageAlias][$phrase])) {
            return $this->allTranslates[$languageAlias][$phrase];
        }

        return null;
    }

    /**
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    protected function preloadTranslates(string $languageAlias,bool $forceLoading = false)
    {
        if (!isset($this->allTranslates[$languageAlias]) || $forceLoading) {
            $this->allTranslates[$languageAlias] = $this->parseLanguageFile($languageAlias);
        }
    }

    /**
     * @throws FileReadPermissionsException
     * @throws DirectoryNotFoundException
     * @throws UnsupportedLanguageAliasException
     */
    protected function parseLanguageFile(string $languageAlias): array
    {
        $translates = [];
        foreach ($this->iterateTranslationFileData($languageAlias) as $languageFileData) {
            $translates[$languageFileData[0]] = $languageFileData[1] ?? '';
        }

        return $translates;
    }

    const INDEXED_BY_ID = 'id';
    const INDEXED_BY_ORIGINAL_CONTENT = 'original';

    public function parseOriginalsLanguageRowsIds(string $indexedBy = self::INDEXED_BY_ID): array
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
            }
            $translates[$key] = $value;
        }

        return $translates;
    }

    /**
     * @throws UnsupportedLanguageAliasException
     */
    protected function saveLanguageFile(string $languageAlias): void
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
    public function saveTranslate(string $languageAlias, string $original, string $translate): void
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
    public function delete(string $original): void
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
    public function saveOriginals(array $phrases): void
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
     * @param int|null $limit
     * @return OriginalPhraseCollection
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function getOriginalsWithoutTranslate(string $translationLanguageAlias, int $offset = 0, int $limit = null): OriginalPhraseCollection
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

    public function getAllOriginalTranslates(string $phrase, ?array $languagesAliases = null): array
    {
        $translations = [];

        $languagesAliases = $languagesAliases ?: $this->getExistedTranslationLanguages();
        foreach ($languagesAliases as $languagesAlias) {
            $this->preloadTranslates($languagesAlias);

            if (!empty($this->allTranslates[$languagesAlias][$phrase])) {
                $translations[$languagesAlias] = $this->allTranslates[$languagesAlias][$phrase];
            }
        }

        return $translations;
    }

    /**
     * @param $languageAlias
     * @return Generator|array
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
