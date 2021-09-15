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
    protected $isoCode;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @param string $isoCode
     * @param string $title
     * @param null|string $alias
     */
    public function __construct(string $isoCode, string $title = '', $alias = null)
    {
        $this->isoCode = $isoCode;
        $this->title = $title;
        $this->alias = $alias ?: $isoCode;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function __toString(): string
    {
        return $this->getIsoCode();
    }
}
