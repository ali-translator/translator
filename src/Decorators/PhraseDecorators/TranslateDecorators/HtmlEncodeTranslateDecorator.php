<?php

namespace ALI\Translator\Decorators\PhraseDecorators\TranslateDecorators;

class HtmlEncodeTranslateDecorator implements TranslatePhraseDecorator
{
    protected string $charset;

    public function __construct(string $charset = 'UTF-8')
    {
        $this->charset = $charset;
    }

    /**
     * @param string $original
     * @param string $translate
     * @return string
     */
    public function decorate($original, $translate): string
    {
        return htmlspecialchars($translate, ENT_QUOTES, $this->charset, false);
    }
}
