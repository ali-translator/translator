<?php

namespace ALI\Translator\Event;

use ALI\Translator\Event\EnumList\EventType;

class EventDispatcher implements EventDispatcherInterface
{
    private int $lastListenerId = 0;

    /**
     * @var Array<string, Array<string, callable>>
     */
    private array $listeners = [];

    /**
     * @param string $eventTypeId
     * @see EventType
     * @inheritDoc
     */
    public function addListener(string $eventTypeId, callable $listener): int
    {
        $this->listeners[$eventTypeId][$this->lastListenerId++] = $listener;

        return $this->lastListenerId;
    }

    public function removeListener(string $eventTypeId, int $listenerId): void
    {
        unset($this->listeners[$eventTypeId][$listenerId]);
    }

    public function dispatch(Event $event): void
    {
        $eventType = $event->getEventType();

        $listeners = $this->listeners[$eventType->getId()] ?? [];
        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
