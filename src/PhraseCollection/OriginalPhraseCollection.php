<?php

namespace ALI\Translator\PhraseCollection;

use ArrayIterator;
use IteratorAggregate;
use IteratorIterator;
use Traversable;

/**
 * OriginalPhrasePacket
 */
class OriginalPhraseCollection implements IteratorAggregate
{
    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var string[]
     */
    protected $originals;

    /**
     * @param string $originalLanguageAlias
     * @param string[] $originals
     */
    public function __construct(
        $originalLanguageAlias,
        array $originals = []
    )
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->originals = $originals;
    }

    /**
     * @return string
     */
    public function getOriginalLanguageAlias()
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @param string $content
     */
    public function add($content)
    {
        $this->originals[$content] = $content;
    }

    /**
     * @param string $content
     * @return bool
     */
    public function exist($content)
    {
        return isset($this->originals[$content]);
    }

    /**
     * @param $content
     */
    public function remove($content)
    {
        if (isset($this->originals[$content])) {
            unset($this->originals[$content]);
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->originals);
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        return array_values($this->originals);
    }

    /**
     * @return IteratorIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->originals);
    }
}
