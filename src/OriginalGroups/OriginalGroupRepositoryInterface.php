<?php

namespace ALI\Translator\OriginalGroups;

use ALI\Translator\Source\SourceInterface;

interface OriginalGroupRepositoryInterface
{
    public function addGroups(array $originals, array $groups);

    public function removeGroups(array $originals, array $groups);

    public function removeAllGroups(array $originals);

    public function getOriginalsByGroup(string $groupAlias, int $offset = 0, int $limit = 100): array;

    public function getGroups(array $originalsContent): array;

    public function getTranslatorSource(): SourceInterface;

    public function generateInstaller(): OriginalGroupRepositoryInstallerInterface;
}
