<?php

namespace ALI\Translator\Event\EnumList;

class EventTimeOrder
{
    const BEFORE = 'before';
    const AFTER = 'after';

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
