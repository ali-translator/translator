<?php

namespace ALI\Translator\Decorators\PhraseDecorators\OriginalDecorators;

interface OriginalPhraseDecoratorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function decorate(string $original): string;
}
