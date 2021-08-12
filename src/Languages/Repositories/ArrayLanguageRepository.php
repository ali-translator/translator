<?php

namespace ALI\Translator\Languages\Repositories;

use ALI\Translator\Languages\LanguageInterface;
use ALI\Translator\Languages\LanguageRepositoryInterface;

/**
 * This repository may use, when your save your languages in config file,
 * and create object from by config data
 */
class ArrayLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * @var LanguageInterface[]
     */
    protected $activeLanguages = [];

    /**
     * @var LanguageInterface[]
     */
    protected $inactiveLanguages = [];

    /**
     * @inheritDoc
     */
    public function save(LanguageInterface $language,bool $isActive): bool
    {
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
    public function find(string $alias)
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
}
