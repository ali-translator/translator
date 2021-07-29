<?php

namespace ALI\Translator\Languages;

/**
 * Language
 */
class Language implements LanguageInterface
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param string $alias
     * @param string $title
     */
    public function __construct(string $alias, string $title = '')
    {
        $this->alias = $alias;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAlias();
    }
}
