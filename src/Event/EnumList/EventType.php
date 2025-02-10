<?php

namespace ALI\Translator\Event\EnumList;

class EventType
{
    // Supported EventType id
    public const EVENT_SAVE_ORIGINAL_BEFORE = EventDataType::SOURCE_WRITER_SAVE_ORIGINAL . self::ID_DELIMITER . EventTimeOrder::BEFORE;
    public const EVENT_SAVE_ORIGINAL_AFTER = EventDataType::SOURCE_WRITER_SAVE_ORIGINAL . self::ID_DELIMITER . EventTimeOrder::AFTER;

    public const EVENT_SAVE_TRANSLATION_BEFORE = EventDataType::SOURCE_WRITER_SAVE_TRANSLATION . self::ID_DELIMITER . EventTimeOrder::BEFORE;
    public const EVENT_SAVE_TRANSLATION_AFTER = EventDataType::SOURCE_WRITER_SAVE_TRANSLATION . self::ID_DELIMITER . EventTimeOrder::AFTER;

    public const EVENT_DELETE_ORIGINAL_BEFORE = EventDataType::SOURCE_WRITER_DELETE_ORIGINAL . self::ID_DELIMITER . EventTimeOrder::BEFORE;
    public const EVENT_DELETE_ORIGINAL_AFTER = EventDataType::SOURCE_WRITER_DELETE_ORIGINAL . self::ID_DELIMITER . EventTimeOrder::AFTER;


    public const ID_DELIMITER = '#';

    private EventDataType $eventDataType;
    private EventTimeOrder $eventTimeOrder;

    public function __construct(
        EventDataType  $eventDataType,
        EventTimeOrder $eventTimeOrder
    )
    {
        $this->eventDataType = $eventDataType;
        $this->eventTimeOrder = $eventTimeOrder;
    }

    public static function createById(string $id): self
    {
        $explodedId = explode(self::ID_DELIMITER, $id);
        return new self(
            new EventDataType($explodedId[0]),
            new EventTimeOrder($explodedId[1])
        );
    }

    public function getEventDataType(): EventDataType
    {
        return $this->eventDataType;
    }

    public function getEventTimeOrder(): EventTimeOrder
    {
        return $this->eventTimeOrder;
    }

    public function getId(): string
    {
        return  $this->eventDataType->getValue() . self::ID_DELIMITER . $this->eventTimeOrder->getValue();
    }
}
