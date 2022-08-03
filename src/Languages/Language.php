<?php

namespace ALI\Translator\Languages;

/**
 * Language
 */
class Language implements LanguageInterface, LanguageConstructorInterface
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
     * @var array
     */
    private $additionalInformation;

    public function __construct(
        string $isoCode,
        string $title = '',
               $alias = null,
        array  $additionalInformation = []
    )
    {
        $this->isoCode = $isoCode;
        $this->title = $title;
        $this->alias = $alias ?: $isoCode;
        $this->additionalInformation = $additionalInformation;
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

    public function getAdditionalInformation(): array
    {
        return $this->additionalInformation;
    }

    public function __toString(): string
    {
        return $this->getIsoCode();
    }
}
