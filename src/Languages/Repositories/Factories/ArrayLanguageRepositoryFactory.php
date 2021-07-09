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
     * languagesData : ['alias'=>'title',...]
     *
     * @param array $activeLanguagesData
     * @param array $inActiveLanguagesData
     * @return ArrayLanguageRepository
     */
    public function createArrayLanguageRepository(array $activeLanguagesData = [], array $inActiveLanguagesData = [])
    {
        $arrayLanguageRepository = new ArrayLanguageRepository();
        foreach ($activeLanguagesData as $alias => $title) {
            $arrayLanguageRepository->save((new Language($alias, $title)), true);
        }
        foreach ($inActiveLanguagesData as $alias => $title) {
            $arrayLanguageRepository->save((new Language($alias, $title)), false);
        }

        return $arrayLanguageRepository;
    }
}
