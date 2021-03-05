<?php

namespace ALI\Translator\Source\Installers;

use ALI\Translator\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translator\Source\Sources\FileSources\FileSourceAbstract;

/**
 * Class
 */
class FileSourceInstaller implements SourceInstallerInterface
{
    /**
     * @var FileSourceAbstract
     */
    protected $fileSource;

    /**
     * @param FileSourceAbstract $fileSource
     */
    public function __construct(FileSourceAbstract $fileSource)
    {
        $this->fileSource = $fileSource;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        $iterator = $this->fileSource->getGlobIterator();

        return $iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function install()
    {
        if (!file_exists($this->fileSource->getDirectoryPath())) {
            mkdir($this->fileSource->getDirectoryPath(), 0777, true);
        }
        $originalFilePath = $this->getOriginalFilePath();
        if (!file_exists($originalFilePath)) {
            touch($originalFilePath);
        }
    }

    /**
     * @inheritDoc
     */
    public function destroy()
    {
        $iterator = $this->fileSource->getGlobIterator();
        while ($iterator->valid()) {
            unlink($iterator->current()->getPathname());
            $iterator->next();
        }
    }

    /**
     * @return string
     * @throws UnsupportedLanguageAliasException
     */
    public function getOriginalFilePath()
    {
        return $this->fileSource->getLanguageFilePath($this->fileSource->getOriginalLanguageAlias());
    }
}
