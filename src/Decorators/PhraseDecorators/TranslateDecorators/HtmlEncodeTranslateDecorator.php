<?php

namespace ALI\Translator\Decorators\PhraseDecorators\TranslateDecorators;

/**
 * Class
 */
class HtmlEncodeTranslateDecorator implements TranslatePhraseDecorator
{
    /**
     * @var string
     */
    protected $charset;

    /**
     * @param $charset
     */
    public function __construct($charset = 'UTF-8')
    {
        $this->charset = $charset;
    }

    /**
     * @param string $original
     * @param string $translate
     * @return string
     */
    public function decorate($original, $translate)
    {
        return htmlspecialchars($translate, ENT_QUOTES, $this->charset, false);
    }
}
