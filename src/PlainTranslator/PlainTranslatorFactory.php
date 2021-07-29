<?php

namespace ALI\Translator\PlainTranslator;

use ALI\Translator\Source\SourceInterface;
use ALI\Translator\Source\SourcesCollection;
use ALI\Translator\Translator;

/**
 * Class
 */
class PlainTranslatorFactory
{
    /**
     * @param SourceInterface $source
     * @param $translationLanguageAlias
     * @return PlainTranslator
     */
    public function createPlainTranslator(SourceInterface $source, $translationLanguageAlias): PlainTranslator
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
