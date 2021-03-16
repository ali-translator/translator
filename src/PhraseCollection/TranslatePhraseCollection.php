<?php

namespace ALI\Translator\PhraseCollection;

use ArrayIterator;
use IteratorAggregate;

/**
 * TranslatePhrasePacket
 */
class TranslatePhraseCollection implements IteratorAggregate
{
    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var string
     */
    protected $translationLanguageAlias;

    /**
     * @var string[]
     */
    private $originalsWithTranslate;

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias,
     * @param string[] $originalsWithTranslate
     */
    public function __construct(
        $originalLanguageAlias,
        $translationLanguageAlias,
        array $originalsWithTranslate = []
    )
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->translationLanguageAlias = $translationLanguageAlias;
        $this->originalsWithTranslate = $originalsWithTranslate;
    }

    /**
     * @return string
     */
    public function getOriginalLanguageAlias()
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @return string
     */
    public function getTranslationLanguageAlias()
    {
        return $this->translationLanguageAlias;
    }

    /**
     * @param string $original
     * @param string $translate
     */
    public function addTranslate($original, $translate)
    {
        $this->originalsWithTranslate[$original] = $translate;
    }

    /**
     * @param string $original
     * @param bool $withTranslationFallback
     * @return string|null
     */
    public function getTranslate($original, $withTranslationFallback = false)
    {
        if (isset($this->originalsWithTranslate[$original])) {
            $translation = $this->originalsWithTranslate[$original];
        } else {
            $translation = null;
        }

        if ($withTranslationFallback && !$translation) {
            $translation = $original;
        }

        return $translation;
    }

    /**
     * @param string $original
     * @return bool
     */
    public function existOriginal($original)
    {
        return isset($this->originalsWithTranslate[$original]);
    }

    /**
     * @param string $original
     * @return bool
     */
    public function existTranslate($original)
    {
        return !empty($this->originalsWithTranslate[$original]);
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        return $this->originalsWithTranslate;
    }

    /**
     * @return OriginalPhraseCollection
     */
    public function generateOriginalPhraseCollection()
    {
        $allTranslatesPhrases = $this->getAll();
        $originalPhrases = array_values($allTranslatesPhrases);

        return new OriginalPhraseCollection($this->getOriginalLanguageAlias(), $originalPhrases);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->originalsWithTranslate);
    }
}
