<?php

namespace ALI\Translator\Decorators\PhraseDecorators;

use ALI\Translator\Decorators\PhraseDecorators\OriginalDecorators\OriginalPhraseDecoratorInterface;

class OriginalPhraseDecoratorManager implements OriginalPhraseDecoratorInterface
{
    /**
     * @var OriginalPhraseDecoratorInterface[]
     */
    protected $originalDecorators = [];

    /**
     * @param OriginalPhraseDecoratorInterface[] $originalDecorators
     */
    public function __construct(array $originalDecorators = [])
    {
        $this->originalDecorators = $originalDecorators;
    }

    /**
     * @param string $original
     * @return string
     */
    public function decorate(string $original): string
    {
        foreach ($this->originalDecorators as $originalDecorator) {
            $original = $originalDecorator->decorate($original);
        }

        return $original;
    }

    /**
     * @return OriginalPhraseDecoratorInterface[]
     */
    public function getOriginalDecorators(): array
    {
        return $this->originalDecorators;
    }

    /**
     * @param OriginalPhraseDecoratorInterface[] $originalDecorators
     */
    public function setOriginalDecorators(array $originalDecorators)
    {
        $this->originalDecorators = $originalDecorators;
    }
}
