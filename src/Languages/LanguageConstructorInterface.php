<?php

namespace ALI\Translator\Languages;

interface LanguageConstructorInterface
{
    public function __construct(
        string $isoCode,
        string $title = '',
               $alias = null,
        array  $additionalInformation = []
    );
}
