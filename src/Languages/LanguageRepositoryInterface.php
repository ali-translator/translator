<?php

namespace ALI\Translator\Languages;

interface LanguageRepositoryInterface
{
    public function save(LanguageInterface $language, bool $isActive): bool;

    public function find(string $alias): ?LanguageInterface;

    /**
     * @param string[] $aliases
     * @return LanguageInterface[]
     */
    public function findAllByAliases(array $aliases): array;

    public function findByIsoCode(string $isoCode): ?LanguageInterface;

    /**
     * @param string[] $isoCodes
     * @return LanguageInterface[]
     */
    public function findAllByIsoCodes(array $isoCodes): array;

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]
     */
    public function getAll(bool $onlyActive): array;

    /**
     * @return LanguageInterface[]
     */
    public function getInactiveLanguages(): array;

    public function generateInstaller(): LanguageRepositoryInstallerInterface;
}
