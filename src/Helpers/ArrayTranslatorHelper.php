<?php

namespace ALI\Translator\Helpers;

use ALI\Translator\PlainTranslator\PlainTranslatorInterface;

class ArrayTranslatorHelper
{
    public function translateArray(
        array $arrayForTranslation,
        array $columnsForTranslation,
        PlainTranslatorInterface $plainTranslator,
        bool $withFallback
    )
    {
        $phrasesForTranslation = [];
        foreach ($arrayForTranslation as $data) {
            foreach ($columnsForTranslation as $columnForTranslation) {
                $phrasesForTranslation[] = $data[$columnForTranslation];
            }
        }

        $translatePhraseCollection = $plainTranslator->translateAll($phrasesForTranslation);
        foreach ($arrayForTranslation as &$data) {
            foreach ($columnsForTranslation as $columnForTranslation) {
                $data[$columnForTranslation] = $translatePhraseCollection->getTranslate($data[$columnForTranslation], $withFallback);
            }
        }

        return $arrayForTranslation;
    }
}
