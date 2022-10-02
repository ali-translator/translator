<?php

namespace ALI\Translator\Tests\unit;

use ALI\Translator\Tests\components\TranslatorTester;
use ALI\Translator\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function test()
    {
        $translatorTester = new TranslatorTester();
        $sourceCollection = $translatorTester->generateSourceCollection();
        $translator = new Translator($sourceCollection);
        (new TranslatorTester())->test($translator, $this);;
    }
}
