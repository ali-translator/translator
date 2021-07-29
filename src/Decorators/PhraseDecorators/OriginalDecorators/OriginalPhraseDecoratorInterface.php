<?php

namespace ALI\Translator\Decorators\PhraseDecorators\OriginalDecorators;

/**
 * Interface
 */
interface OriginalPhraseDecoratorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function decorate(string $original): string;
}
