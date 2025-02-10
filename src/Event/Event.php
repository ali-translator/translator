<?php

namespace ALI\Translator\Event;

use ALI\Translator\Event\EnumList\EventTimeOrder;
use ALI\Translator\Event\EnumList\EventType;

class Event
{
    private EventTimeOrder $eventTimeOrder;
    private EventDataInterface $eventData;

    public function __construct(EventTimeOrder $eventTimeOrder, EventDataInterface $eventData)
    {
        $this->eventTimeOrder = $eventTimeOrder;
        $this->eventData = $eventData;
    }

    public function getEventType(): EventType
    {
        return new EventType($this->eventData::getType(), $this->eventTimeOrder);
    }

    public function getEventData(): EventDataInterface
    {
        return $this->eventData;
    }
}
