<?php

namespace ALI\Translator\Source\Installers;

/**
 * Interface
 */
interface SourceInstallerInterface
{
    /**
     * @return bool
     */
    public function isInstalled(): bool;

    /**
     * Install
     */
    public function install();

    /**
     * Destroy
     */
    public function destroy();
}
