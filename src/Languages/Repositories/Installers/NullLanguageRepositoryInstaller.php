<?php

namespace ALI\Translator\Languages\Repositories\Installers;

use ALI\Translator\Languages\LanguageRepositoryInstallerInterface;

class NullLanguageRepositoryInstaller implements LanguageRepositoryInstallerInterface
{
    public function isInstalled(): bool
    {
        return true;
    }

    public function install(){
    }

    public function destroy(){
    }
}
