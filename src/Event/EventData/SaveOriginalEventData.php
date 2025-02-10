<?php

namespace ALI\Translator\Event\EventData;

use ALI\Translator\Event\EnumList\EventDataType;
use ALI\Translator\Event\EventDataInterface;

class SaveOriginalEventData implements EventDataInterface
{
    private string $original;

    public function __construct(
        string $original
    )
    {
        $this->original = $original;
    }

    public static function getType(): EventDataType
    {
        return new EventDataType(EventDataType::SOURCE_WRITER_SAVE_ORIGINAL);
    }


    public function getOriginal(): string
    {
        return $this->original;
    }
}
