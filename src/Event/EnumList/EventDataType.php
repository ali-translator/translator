<?php

namespace ALI\Translator\Event\EnumList;

class EventDataType
{
    public const SOURCE_WRITER_SAVE_TRANSLATION = 'sourceWriter.saveTranslation';
    public const SOURCE_WRITER_SAVE_ORIGINAL = 'sourceWriter.saveOriginal';
    public const SOURCE_WRITER_DELETE_ORIGINAL = 'sourceWriter.deleteOriginal';

    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
