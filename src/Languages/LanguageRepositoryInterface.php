<?php

namespace ALI\Translator\Languages;

/**
 * LanguageRepositoryInterface
 */
interface LanguageRepositoryInterface
{
    public function save(LanguageInterface $language, bool $isActive): bool;

    /**
     * @param string $alias
     * @return null|LanguageInterface
     */
    public function find(string $alias);

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]
     */
    public function getAll(bool $onlyActive): array;

    /**
     * @return LanguageInterface[]
     */
    public function getInactiveLanguages(): array;
}
