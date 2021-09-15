<?php

namespace ALI\Translator\Languages\Repositories\Factories;

use ALI\Translator\Languages\Language;
use ALI\Translator\Languages\Repositories\ArrayLanguageRepository;

/**
 * Class
 */
class ArrayLanguageRepositoryFactory
{
    /**
     * languagesData : ['isoCode'=>'Title', 'isoCode'=>['title'=>'Title','alias' => 'Alias'], ...]
     *
     * @param array $activeLanguagesData
     * @param array $inActiveLanguagesData
     * @return ArrayLanguageRepository
     */
    public function createArrayLanguageRepository(array $activeLanguagesData = [], array $inActiveLanguagesData = []): ArrayLanguageRepository
    {
        $arrayLanguageRepository = new ArrayLanguageRepository();
        foreach ($activeLanguagesData as $isoCode => $data) {
            $language = $this->generateLanguageByData($isoCode, $data);
            $arrayLanguageRepository->save($language, true);
        }
        foreach ($inActiveLanguagesData as $isoCode => $data) {
            $language = $this->generateLanguageByData($isoCode, $data);
            $arrayLanguageRepository->save($language, false);
        }

        return $arrayLanguageRepository;
    }

    protected function generateLanguageByData(string $isoCode, $data)
    {
        if (is_string($data)) {
            $title = $data;
            $alias = null;
        } else {
            $title = $data['title'] ?? null;
            $alias = $data['alias'] ?? null;;
        }

        return new Language($isoCode, $title, $alias);
    }
}
