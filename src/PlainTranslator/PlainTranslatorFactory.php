<?php

namespace ALI\Translator\PlainTranslator;

use ALI\Translator\Source\SourceInterface;
use ALI\Translator\Source\SourcesCollection;
use ALI\Translator\Translator;

class PlainTranslatorFactory
{
    public function createPlainTranslator(SourceInterface $source, string $translationLanguageAlias): PlainTranslator
    {
        $sourceCollection = new SourcesCollection();
        $sourceCollection->addSource($source);

        return new PlainTranslator(
            $source->getOriginalLanguageAlias(),
            $translationLanguageAlias,
            new Translator($sourceCollection)
        );
    }
}
