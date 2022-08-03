<?php

namespace ALI\Translator\Tests\unit\Languages;

use ALI\Translator\Languages\Language;
use ALI\Translator\Languages\LanguageInterface;
use ALI\Translator\Tests\components\Factories\LanguageRepositoryFactory;
use ALI\Translator\Tests\components\Factories\LanguagesEnum;
use PHPUnit\Framework\TestCase;

class LanguageRepositoryTest extends TestCase
{
    public function test()
    {
        $originalLanguage = LanguagesEnum::ORIGINAL_LANGUAGE_ALIAS;
        foreach ((new LanguageRepositoryFactory())->iterateAllRepository($originalLanguage) as $languageRepository) {

            self::assertEquals(true, $languageRepository->generateInstaller()->isInstalled());

            $additionalUKInformation = ['is_the_best_language' => true];
            $languageUk = new Language('uk', 'Ukraine', 'ua' , $additionalUKInformation);
            $languageRepository->save($languageUk, true);
            $languageEn = new Language('en', 'English');
            $languageRepository->save($languageEn, false);

            $existedLanguageUk = $languageRepository->find($languageUk->getAlias());
            self::assertEquals($languageUk, $existedLanguageUk);

            /** @var LanguageInterface $existedLanguageUk */
            $existedLanguageUk = $languageRepository->findByIsoCode($languageUk->getIsoCode());
            self::assertEquals($languageUk, $existedLanguageUk);
            self::assertEquals($additionalUKInformation, $existedLanguageUk->getAdditionalInformation());

            $existedLanguageEn = $languageRepository->find($languageEn->getAlias());
            self::assertEquals($languageEn, $existedLanguageEn);

            /** @var LanguageInterface $existedLanguageEn */
            $existedLanguageEn = $languageRepository->findByIsoCode($languageEn->getIsoCode());
            self::assertEquals($languageEn, $existedLanguageEn);
            self::assertEquals([], $existedLanguageEn->getAdditionalInformation());
        }
    }
}
