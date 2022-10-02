<?php

namespace ALI\Translator\Languages\Repositories\Factories;

use ALI\Translator\Languages\Language;
use ALI\Translator\Languages\LanguageConstructorInterface;
use ALI\Translator\Languages\LanguageInterface;
use ALI\Translator\Languages\Repositories\ArrayLanguageRepository;

class ArrayLanguageRepositoryFactory
{
    /**
     * languagesData : ['isoCode'=>'Title', 'isoCode'=> [ 'title'=>'Title', 'alias' => 'Alias','additionalInformation' => []], ...]
     *
     * @param array $activeLanguagesData
     * @param array $inActiveLanguagesData
     * @param string|LanguageInterface|LanguageConstructorInterface $languageEntityClass
     * @return ArrayLanguageRepository
     */
    public function createArrayLanguageRepository(
        array $activeLanguagesData = [],
        array $inActiveLanguagesData = [],
        string $languageEntityClass = Language::class
    ): ArrayLanguageRepository
    {
        $arrayLanguageRepository = new ArrayLanguageRepository();
        foreach ($activeLanguagesData as $isoCode => $data) {
            $language = $this->generateLanguageByData($isoCode, $data, $languageEntityClass);
            $arrayLanguageRepository->save($language, true);
        }
        foreach ($inActiveLanguagesData as $isoCode => $data) {
            $language = $this->generateLanguageByData($isoCode, $data, $languageEntityClass);
            $arrayLanguageRepository->save($language, false);
        }

        return $arrayLanguageRepository;
    }

    /**
     * @param string $isoCode
     * @param array|string $data
     * @param string|LanguageInterface|LanguageConstructorInterface $languageEntityClass
     * @return LanguageInterface
     */
    protected function generateLanguageByData(
        string $isoCode,
        $data,
        string $languageEntityClass
    ): LanguageInterface
    {
        if (is_string($data)) {
            $title = $data;
            $alias = null;
            $additionalInformation = [];
        } else {
            $title = $data['title'] ?? null;
            $alias = $data['alias'] ?? null;
            $additionalInformation = $data['additionalInformation'] ?? [];
        }

        return new $languageEntityClass($isoCode, $title, $alias, $additionalInformation);
    }
}
