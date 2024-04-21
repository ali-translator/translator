<?php

namespace ALI\Translator\Languages;

interface LanguageInterface
{
    /**
     * Language title (Русский, English)
     */
    public function getTitle(): string;

    /**
     * Language iso code (ru, en, uk)
     */
    public function getIsoCode(): string;

    /**
     * For example, use 'ua' alias for language with 'uk' iso code
     */
    public function getAlias(): string;

    public function getAdditionalInformation(): array;
}
