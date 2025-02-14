<?php

namespace ALI\Translator\Languages\Repositories;

use ALI\Translator\Languages\LanguageInterface;
use ALI\Translator\Languages\LanguageRepositoryInstallerInterface;
use ALI\Translator\Languages\LanguageRepositoryInterface;
use ALI\Translator\Languages\Repositories\Installers\NullLanguageRepositoryInstaller;

class ArrayLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * @var LanguageInterface[]
     */
    protected array $activeLanguages = [];

    /**
     * @var LanguageInterface[]
     */
    protected array $inactiveLanguages = [];

    protected array $isoCodeVsAlias = [];

    public function save(LanguageInterface $language, bool $isActive): bool
    {
        $this->isoCodeVsAlias[$language->getIsoCode()] = $language->getAlias();

        if ($isActive) {
            $this->activeLanguages[$language->getAlias()] = $language;
            if (isset($this->inactiveLanguages[$language->getAlias()])) {
                unset($this->inactiveLanguages[$language->getAlias()]);
            }
        } else {
            $this->inactiveLanguages[$language->getAlias()] = $language;
            if (isset($this->activeLanguages[$language->getAlias()])) {
                unset($this->activeLanguages[$language->getAlias()]);
            }
        }

        return true;
    }

    /**
     * @param string $alias
     * @return LanguageInterface|null
     */
    public function find(string $alias): ?LanguageInterface
    {
        if (isset($this->activeLanguages[$alias])) {
            return $this->activeLanguages[$alias];
        }

        if (isset($this->inactiveLanguages[$alias])) {
            return $this->inactiveLanguages[$alias];
        }

        return null;
    }

    /**
     * @param string[] $aliases
     * @return LanguageInterface[]
     */
    public function findAllByAliases(array $aliases): array
    {
        $languages = [];
        foreach ($aliases as $alias) {
            $languages[] = $this->find($alias);
        }

        return $languages;
    }

    /**
     * @param string $isoCode
     * @return LanguageInterface|null
     */
    public function findByIsoCode(string $isoCode): ?LanguageInterface
    {
        $languageAlias = $this->isoCodeVsAlias[$isoCode] ?? null;
        if (!$languageAlias) {
            return null;
        }

        return $this->find($languageAlias);
    }

    /**
     * @param string[] $isoCodes
     * @return LanguageInterface[]
     */
    public function findAllByIsoCodes(array $isoCodes): array
    {
        $languages = [];
        foreach ($isoCodes as $isoCode) {
            $languages[] = $this->findByIsoCode($isoCode);
        }

        return $languages;
    }

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]
     */
    public function getAll(bool $onlyActive): array
    {
        if ($onlyActive) {
            return array_values($this->activeLanguages);
        }

        return array_values($this->activeLanguages + $this->inactiveLanguages);
    }

    /**
     * @return LanguageInterface[]
     */
    public function getInactiveLanguages(): array
    {
        return $this->inactiveLanguages;
    }

    /**
     * @inheritDoc
     */
    public function generateInstaller(): LanguageRepositoryInstallerInterface
    {
        return new NullLanguageRepositoryInstaller();
    }
}
