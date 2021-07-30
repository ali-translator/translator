<?php


namespace ALI\Translator\Source;

use ALI\Translator\Source\Installers\SourceInstallerInterface;

/**
 * SourceInterface Interface
 */
interface SourceInterface extends SourceIdWorkerInterface, SourceReaderInterface, SourceWriterInterface
{
    /**
     * @return string
     */
    public function getOriginalLanguageAlias(): string;

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount(): bool;

    /**
     * @return SourceInstallerInterface
     */
    public function generateInstaller(): SourceInstallerInterface;
}
