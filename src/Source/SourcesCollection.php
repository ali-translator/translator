<?php

namespace ALI\Translator\Source;

/**
 * Class
 */
class SourcesCollection
{
    const ALL_LANGUAGES = 'all';

    /**
     * @var SourceInterface[]
     */
    protected $sources = [];

    /**
     * @param SourceInterface $source
     * @param array $languagesAliasesForTranslate
     */
    public function addSource(SourceInterface $source, array $languagesAliasesForTranslate = [])
    {
        if (!$languagesAliasesForTranslate) {
            $this->sources[$source->getOriginalLanguageAlias()][self::ALL_LANGUAGES] = $source;
        } else {
            foreach ($languagesAliasesForTranslate as $languageAlias) {
                $this->sources[$source->getOriginalLanguageAlias()][$languageAlias] = $source;
            }
        }
    }

    /**
     * @param string $originalLanguageAlias
     * @param string $translateLanguageAlias
     * @return null|SourceInterface
     */
    public function getSource($originalLanguageAlias, $translateLanguageAlias = null)
    {
        if (!isset($this->sources[$originalLanguageAlias])) {
            return null;
        }
        if ($translateLanguageAlias && isset($this->sources[$originalLanguageAlias][$translateLanguageAlias])) {
            return $this->sources[$originalLanguageAlias][$translateLanguageAlias];
        }
        if (isset($this->sources[$originalLanguageAlias][self::ALL_LANGUAGES])) {
            return $this->sources[$originalLanguageAlias][self::ALL_LANGUAGES];
        }
        if (!$translateLanguageAlias) {
            $firstSource = current($this->sources[$originalLanguageAlias]);
            if ($firstSource) {
                return $firstSource;
            }
        }

        return null;
    }
}
