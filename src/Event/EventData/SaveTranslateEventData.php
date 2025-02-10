<?php

namespace ALI\Translator\Event\EventData;

use ALI\Translator\Event\EnumList\EventDataType;
use ALI\Translator\Event\EventDataInterface;

class SaveTranslateEventData implements EventDataInterface
{
    private string $languageAlias;
    private string $original;
    private string $translate;

    public function __construct(
        string $languageAlias, string $original, string $translate
    )
    {
        $this->languageAlias = $languageAlias;
        $this->original = $original;
        $this->translate = $translate;
    }

    public static function getType(): EventDataType
    {
        return new EventDataType(EventDataType::SOURCE_WRITER_SAVE_TRANSLATION);
    }

    public function getLanguageAlias(): string
    {
        return $this->languageAlias;
    }

    public function getOriginal(): string
    {
        return $this->original;
    }

    public function getTranslate(): string
    {
        return $this->translate;
    }
}
