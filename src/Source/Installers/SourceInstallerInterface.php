<?php

namespace ALI\Translator\Source\Installers;

interface SourceInstallerInterface
{
    public function isInstalled(): bool;

    public function install();

    public function destroy();
}
