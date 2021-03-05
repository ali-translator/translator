<?php

namespace ALI\Translator\Decorators\PhraseDecorators;

use ALI\Translator\Decorators\PhraseDecorators\TranslateDecorators\TranslatePhraseDecorator;

/**
 * Class
 */
class TranslatePhraseDecoratorManager implements TranslatePhraseDecorator
{
    /**
     * @var TranslatePhraseDecorator[]
     */
    protected $translateDecorators = [];

    /**
     * @param TranslatePhraseDecorator[] $translateDecorators
     */
    public function __construct(array $translateDecorators = [])
    {
        $this->translateDecorators = $translateDecorators;
    }

    /**
     * @param string $original
     * @param string $translate
     * @return string - translate string
     */
    public function decorate($original, $translate)
    {
        foreach ($this->translateDecorators as $translateDecorator) {
            $translate = $translateDecorator->decorate($original, $translate);
        }

        return $translate;
    }

    /**
     * @return TranslatePhraseDecorator[]
     */
    public function getTranslateDecorators()
    {
        return $this->translateDecorators;
    }

    /**
     * @param TranslatePhraseDecorator[] $translateDecorators
     */
    public function setTranslateDecorators($translateDecorators)
    {
        $this->translateDecorators = $translateDecorators;
    }
}
