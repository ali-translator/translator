<?php

namespace ALI\Translator\Languages;

interface LanguageRepositoryInstallerInterface
{
    public function isInstalled(): bool;

    public function install();

    public function destroy();
}
