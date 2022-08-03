<?php

namespace ALI\Translator\Tests\unit\Languages;

use ALI\Translator\Languages\Repositories\Factories\ArrayLanguageRepositoryFactory;
use PHPUnit\Framework\TestCase;

class ArrayLanguageRepositoryFactoryTest extends TestCase
{
    public function test()
    {
        $languages = [
            'active' => [
                'uk' => [
                    'title' => 'UA',
                    'additionalInformation' => ['is_the_best_language' => true],
                ],
                'en' => 'EN',
            ],
            'inactive' => [
                'ru' => 'RU',
            ],
        ];

        $arrayLanguageRepositoryFactory = new ArrayLanguageRepositoryFactory();
        $repository = $arrayLanguageRepositoryFactory->createArrayLanguageRepository($languages['active'], $languages['inactive']);

        $language = $repository->find('uk');
        self::assertEquals(['is_the_best_language' => true], $language->getAdditionalInformation());

        $language = $repository->find('en');
        self::assertEquals([], $language->getAdditionalInformation());

        $languages = $repository->getAll(false);
        self::assertEquals(3, count($languages));
        $languages = $repository->getAll(true);
        self::assertEquals(2, count($languages));
        $languages = $repository->getInactiveLanguages();
        self::assertEquals(1, count($languages));
    }
}
