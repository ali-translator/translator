<?php

namespace ALI\Translator\PhraseCollection;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

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
     * @param string $translationLanguageAlias ,
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
    public function getOriginalLanguageAlias(): string
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @return string
     */
    public function getTranslationLanguageAlias(): string
    {
        return $this->translationLanguageAlias;
    }

    /**
     * @param string $original
     * @param null|string $translate
     */
    public function addTranslate(string $original, $translate)
    {
        $this->originalsWithTranslate[$original] = $translate;
    }

    /**
     * @param string $original
     * @param bool $withTranslationFallback
     * @return string|null
     */
    public function getTranslate(string $original, bool $withTranslationFallback)
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
    public function existOriginal(string $original): bool
    {
        return isset($this->originalsWithTranslate[$original]);
    }

    /**
     * @param string $original
     * @return bool
     */
    public function existTranslate(string $original): bool
    {
        return !empty($this->originalsWithTranslate[$original]);
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return $this->originalsWithTranslate;
    }

    /**
     * @return OriginalPhraseCollection
     */
    public function generateOriginalPhraseCollection(): OriginalPhraseCollection
    {
        $allTranslatesPhrases = $this->getAll();
        $originalPhrases = array_values($allTranslatesPhrases);

        return new OriginalPhraseCollection($this->getOriginalLanguageAlias(), $originalPhrases);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->originalsWithTranslate);
    }
}
