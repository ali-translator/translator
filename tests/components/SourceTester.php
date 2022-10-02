<?php

namespace ALI\Translator\Tests\components;

use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\SourceInterface;
use PHPUnit\Framework\TestCase;

class SourceTester
{
    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @throws SourceException
     */
    public function testSource(SourceInterface $source, TestCase $testCase)
    {
        $languageForTranslateAlias = 'ua';

        $originalPhrase = 'Hello';
        $translatePhrase = 'Привіт';

        $this->testSourceAddingNewTranslates($source, $testCase, $languageForTranslateAlias, $originalPhrase, $translatePhrase);
        $this->testSourceRemovingTranslate($source, $testCase, $originalPhrase, $languageForTranslateAlias);
        $this->testSourceAddingOriginals($source, $testCase);
        $this->testLongTexts($source, $testCase, $languageForTranslateAlias);
    }

    private function testLongTexts(SourceInterface $source, TestCase $testCase, $languageForTranslateAlias)
    {
        $firstBytes = str_repeat('azgSGZCX', 1000);
        $textForSearching = null;
        $translationForTextForSearching = null;

        $originals = [];
        $translations = [];
        for ($i = 0; $i < 100; $i++) {
            $translationContent = microtime(true) . rand(0, 100);
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

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @param $originalPhrase
     * @param string $languageForTranslateAlias
     * @throws SourceException
     */
    private function testSourceRemovingTranslate(SourceInterface $source, TestCase $testCase, $originalPhrase, $languageForTranslateAlias)
    {
        $source->delete($originalPhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslateAlias);

        $testCase->assertEquals('', $translatePhraseFromSource);
    }

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @param string $languageForTranslateAlias
     * @param $originalPhrase
     * @param $translatePhrase
     * @throws SourceException
     */
    private function testSourceAddingNewTranslates(SourceInterface $source, TestCase $testCase, $languageForTranslateAlias, $originalPhrase, $translatePhrase)
    {
        $source->saveTranslate($languageForTranslateAlias, $originalPhrase, $translatePhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslateAlias);

        $testCase->assertEquals($translatePhrase, $translatePhraseFromSource);
    }

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     */
    private function testSourceAddingOriginals(SourceInterface $source, TestCase $testCase)
    {
        $originals = [
            'A picture is worth 1000 words',
            'Actions speak louder than words',
            'Barking up the wrong tree',
        ];
        $source->saveOriginals($originals);

        // All originals must be exist
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
