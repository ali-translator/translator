<?php

namespace ALI\Translator\Source\Sources\FileSources;

use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\Installers\FileSourceInstaller;
use ALI\Translator\Source\Installers\SourceInstallerInterface;
use ALI\Translator\Source\SourceInterface;

/**
 * FileSourceAbstract
 */
abstract class FileSourceAbstract implements SourceInterface
{
    /**
     * @var string
     */
    protected $directoryPath;

    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var string
     */
    protected $filesExtension;

    /**
     * @return string
     */
    public function getOriginalLanguageAlias()
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @return string
     */
    public function getDirectoryPath()
    {
        return $this->directoryPath;
    }

    /**
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     * @throws SourceException
     */
    public function getTranslates(array $phrases, $languageAlias)
    {
        $translatePhrases = [];
        foreach ($phrases as $phrase) {
            $translatePhrases[$phrase] = $this->getTranslate($phrase, $languageAlias);
        }

        return $translatePhrases;
    }

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount()
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getExistedTranslationLanguages()
    {
        $translateAliases = [];

        $globIterator = $this->getGlobIterator();
        while ($globIterator->valid()) {
            list(, $translateAlias) = explode('_', $globIterator->current()->getBasename('.csv'));
            $translateAliases[] = $translateAlias;
            $globIterator->next();
        }

        return $translateAliases;
    }

    /**
     * @param $languageAlias
     * @return string
     * @throws UnsupportedLanguageAliasException
     */
    public function getLanguageFilePath($languageAlias)
    {
        if (preg_match('#[^\w_\-]#uis', $languageAlias)) {
            throw new UnsupportedLanguageAliasException('Unsupported language alias');
        }

        return $this->getDirectoryPath() . DIRECTORY_SEPARATOR . $this->originalLanguageAlias . '_' . $languageAlias . '.' . $this->filesExtension;
    }

    /**
     * @return \GlobIterator
     */
    public function getGlobIterator()
    {
        return new \GlobIterator($this->directoryPath . DIRECTORY_SEPARATOR . $this->getOriginalLanguageAlias() . '*.' . $this->filesExtension);
    }

    /**
     * @return SourceInstallerInterface|FileSourceInstaller
     */
    public function generateInstaller()
    {
        return new FileSourceInstaller($this);
    }
}
