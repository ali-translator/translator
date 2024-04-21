<?php

namespace ALI\Translator\Languages;

class Language implements LanguageInterface, LanguageConstructorInterface
{
    protected string $isoCode;
    protected string $title;
    protected string $alias;
    private array $additionalInformation;

    public function __construct(
        string $isoCode,
        string $title = '',
        ?string $alias = null,
        array $additionalInformation = []
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
