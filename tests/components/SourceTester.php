<?php

namespace ALI\Translator\Tests\components;

use ALI\Translator\Source\Exceptions\SourceException;
use ALI\Translator\Source\SourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * SourceTester
 */
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
    }

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @param $originalPhrase
     * @param string $languageForTranslateAlias
     * @throws SourceException
     */
    private function testSourceRemovingTranslate(SourceInterface $source, TestCase $testCase, $originalPhrase,$languageForTranslateAlias)
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
