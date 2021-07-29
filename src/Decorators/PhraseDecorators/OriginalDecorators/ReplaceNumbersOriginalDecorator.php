<?php

namespace ALI\Translator\Decorators\PhraseDecorators\OriginalDecorators;

/**
 * Class
 */
class ReplaceNumbersOriginalDecorator implements OriginalPhraseDecoratorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function decorate(string $original): string
    {
        return preg_replace('!\d+!', '0', $original);
    }
}
