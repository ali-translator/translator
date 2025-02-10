<?php

namespace ALI\Translator\Tests\components;

use ALI\Translator\Source\SourceInterface;
use PHPUnit\Framework\TestCase;

class SourceTester
{
    public function testSource(SourceInterface $source, TestCase $testCase)
    {
        $languageForTranslateAlias = 'ua';

        $originalPhrase = 'Hello';
        $translatePhrase = 'Привіт';

        $this->testOriginalsWithEndSpaces($source, $testCase);
        $this->testSourceAddingNewTranslates($source, $testCase, $languageForTranslateAlias, $originalPhrase, $translatePhrase);
        $this->testSourceRemovingTranslate($source, $testCase, $originalPhrase, $languageForTranslateAlias);
        $this->testSourceAddingOriginals($source, $testCase);
        $this->testLongTexts($source, $testCase, $languageForTranslateAlias);
    }

    private function testOriginalsWithEndSpaces(SourceInterface $source, TestCase $testCase)
    {
        $firstOriginalPhrase = 'test';
        $secondOriginalPhrase = 'test ';

        $source->saveOriginals([$firstOriginalPhrase]);
        $source->saveOriginals([$secondOriginalPhrase]);

        $existOriginals = $source->getExistOriginals([$firstOriginalPhrase, $secondOriginalPhrase]);
        $testCase->assertEquals([$firstOriginalPhrase,$secondOriginalPhrase], $existOriginals);

        $originalsIds = $source->getOriginalsIds([$firstOriginalPhrase, $secondOriginalPhrase]);
        $testCase->assertCount(2, $originalsIds);

        $source->delete($firstOriginalPhrase);
        $originalsIds = $source->getOriginalsIds([$firstOriginalPhrase, $secondOriginalPhrase]);
        $testCase->assertCount(1, $originalsIds);
    }

    private function testLongTexts(SourceInterface $source, TestCase $testCase, $languageForTranslateAlias)
    {
        $firstBytes = str_repeat('azgSGZCX', 1000);
        $textForSearching = null;
        $translationForTextForSearching = null;

        $originals = [];
        $translations = [];
        for ($i = 0; $i < 100; $i++) {
            $translationContent = microtime(true) . rand(0, 10000000);
            $originalContent = $firstBytes . $translationContent;
            if ($i === 50) {
                $textForSearching = $originalContent;
                $translationForTextForSearching = $translationContent;
            }
            $originals[] = $originalContent;
            $translations[$originalContent] = $translationContent;
        }
        $source->saveOriginals($originals);

        $existOriginals = $source->getExistOriginals([$textForSearching]);
        $testCase->assertEquals(1, count($existOriginals));
        $testCase->assertEquals($textForSearching, reset($existOriginals));

        foreach ($translations as $original => $translation) {
            $source->saveTranslate($languageForTranslateAlias, $original, $translation);
        }

        $translation = $source->getTranslate($textForSearching, $languageForTranslateAlias);
        $testCase->assertEquals($translationForTextForSearching, $translation);
    }

    private function testSourceRemovingTranslate(SourceInterface $source, TestCase $testCase, $originalPhrase, string $languageForTranslateAlias)
    {
        $source->delete($originalPhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslateAlias);

        $testCase->assertEquals('', $translatePhraseFromSource);
    }

    private function testSourceAddingNewTranslates(SourceInterface $source, TestCase $testCase, string $languageForTranslateAlias, string $originalPhrase, string $translatePhrase)
    {
        $source->saveTranslate($languageForTranslateAlias, $originalPhrase, $translatePhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslateAlias);

        $testCase->assertEquals($translatePhrase, $translatePhraseFromSource);
    }

    private function testSourceAddingOriginals(SourceInterface $source, TestCase $testCase)
    {
        $originals = [
            'A picture is worth 1000 words',
            'Actions speak louder than words',
            'Barking up the wrong tree',
        ];
        $source->saveOriginals($originals);

        // All originals must be existed
        $existOriginals = $source->getExistOriginals($originals);
        $testCase->assertEquals($originals, $existOriginals);

        foreach ($originals as $original) {
            $source->delete($original);
        }

        // Without originals
        $existOriginals = $source->getExistOriginals($originals);
        $testCase->assertEquals([], $existOriginals);
    }
}
