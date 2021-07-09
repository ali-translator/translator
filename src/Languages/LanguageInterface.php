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
     * Language alias (ru, en)
     * @return string
     */
    public function getAlias(): string;
}
