<?php

namespace ALI\Translator\OriginalGroups;

interface OriginalGroupRepositoryInstallerInterface
{
    public function isInstalled(): bool;

    public function install();

    public function destroy();
}
