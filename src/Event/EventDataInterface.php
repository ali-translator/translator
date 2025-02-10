<?php

namespace ALI\Translator\Event;

use ALI\Translator\Event\EnumList\EventDataType;

interface EventDataInterface
{
    public static function getType(): EventDataType;
}
