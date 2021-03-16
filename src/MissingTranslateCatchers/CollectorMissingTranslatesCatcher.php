<?php

namespace ALI\Translator\MissingTranslateCatchers;

use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\PlainTranslator\PlainTranslatorInterface;

/**
 * Class
 */
class CollectorMissingTranslatesCatcher
{
    /**
     * @var OriginalPhraseCollection
     */
    private $originalPhraseCollection;

    /**
     * @param OriginalPhraseCollection $originalPhrasePacket
     */
    public function __construct(OriginalPhraseCollection $originalPhrasePacket)
    {
        $this->originalPhraseCollection = $originalPhrasePacket;
    }

    /**
     * @param string $searchPhrase
     * @param PlainTranslatorInterface $translator
     */
    public function __invoke($searchPhrase, $translator)
    {
        $this->originalPhraseCollection->add($searchPhrase);
    }

    /**
     * @return OriginalPhraseCollection
     */
    public function getOriginalPhraseCollection()
    {
        return $this->originalPhraseCollection;
    }
}
