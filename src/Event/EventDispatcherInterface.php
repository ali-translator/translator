<?php

namespace ALI\Translator\Event;

interface EventDispatcherInterface
{
    /**
     * @return int - listener id
     */
    public function addListener(string $eventTypeId, callable $listener): int;
    public function removeListener(string $eventTypeId, int $listenerId): void;

    public function dispatch(Event $event): void;
}
