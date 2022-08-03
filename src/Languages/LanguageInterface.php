<?php

namespace ALI\Translator\Languages;

/**
 * LanguageInterface Interface
 */
interface LanguageInterface
{
    /**
     * Language title (Русский, English)
     * @return string
     */
    public function getTitle(): string;

    /**
     * Language iso code (ru, en, uk)
     * @return string
     */
    public function getIsoCode(): string;

    /**
     * For example, use 'ua' alias for language with 'uk' iso code
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * @return array
     */
    public function getAdditionalInformation(): array;
}
