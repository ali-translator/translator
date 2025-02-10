<?php

namespace ALI\Translator\Tests\unit\Sources;

use ALI\Translator\Event\EnumList\EventType;
use ALI\Translator\Event\Event;
use ALI\Translator\Event\EventData\SaveTranslateEventData;
use ALI\Translator\Event\EventDispatcher;
use ALI\Translator\Event\EventDispatcherInterface;
use ALI\Translator\Source\Sources\EventDriven\EventDrivenSource;
use ALI\Translator\Tests\Components\Factories\SourceFactory;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventDrivenSourceTest extends TestCase
{
    public function testSaveTranslateDispatchesEvents(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        list($dispatcher, $eventDrivenSource) = $this->generateDispatcherAndEventSource();

        /** @var Event[] $acceptedEvents */
        $acceptedEvents = [];
        $dispatcher->addListener(EventType::EVENT_SAVE_TRANSLATION_BEFORE, $this->generateEventsCollector($acceptedEvents));
        $dispatcher->addListener(EventType::EVENT_SAVE_TRANSLATION_AFTER, $this->generateEventsCollector($acceptedEvents));

        $actualEventData = new SaveTranslateEventData('en', 'test phrase', 'test translation');
        $eventDrivenSource->saveTranslate($actualEventData->getLanguageAlias(), $actualEventData->getOriginal(), $actualEventData->getTranslate());

        $this->assertCount(2, $acceptedEvents);

        $this->assertEquals($acceptedEvents[0]->getEventType()->getId(), EventType::EVENT_SAVE_TRANSLATION_BEFORE);
        $this->assertEquals($acceptedEvents[0]->getEventData(), $actualEventData);
        $this->assertEquals($acceptedEvents[1]->getEventType()->getId(), EventType::EVENT_SAVE_TRANSLATION_AFTER);
        $this->assertEquals($acceptedEvents[1]->getEventData(), $actualEventData);
    }

    /**
     * Перевіряємо, що при виклику saveOriginals() відправляються потрібні події
     */
    public function testSaveOriginalsDispatchesEvents(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        list($dispatcher, $eventDrivenSource) = $this->generateDispatcherAndEventSource();

        /** @var Event[] $acceptedEvents */
        $acceptedEvents = [];
        $dispatcher->addListener(EventType::EVENT_SAVE_ORIGINAL_BEFORE, $this->generateEventsCollector($acceptedEvents));
        $dispatcher->addListener(EventType::EVENT_SAVE_ORIGINAL_AFTER, $this->generateEventsCollector($acceptedEvents));

        $eventDrivenSource->saveOriginals(['phrase_one', 'phrase_two']);

        $this->assertCount(4, $acceptedEvents);
        $this->assertEquals($acceptedEvents[0]->getEventType()->getId(), EventType::EVENT_SAVE_ORIGINAL_BEFORE);
        $this->assertEquals($acceptedEvents[0]->getEventData()->getOriginal(), 'phrase_one');
        $this->assertEquals($acceptedEvents[1]->getEventType()->getId(), EventType::EVENT_SAVE_ORIGINAL_BEFORE);
        $this->assertEquals($acceptedEvents[1]->getEventData()->getOriginal(), 'phrase_two');
        $this->assertEquals($acceptedEvents[2]->getEventType()->getId(), EventType::EVENT_SAVE_ORIGINAL_AFTER);
        $this->assertEquals($acceptedEvents[2]->getEventData()->getOriginal(), 'phrase_one');
        $this->assertEquals($acceptedEvents[3]->getEventType()->getId(), EventType::EVENT_SAVE_ORIGINAL_AFTER);
        $this->assertEquals($acceptedEvents[3]->getEventData()->getOriginal(), 'phrase_two');
    }

    public function testDeleteDispatchesEvents(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        list($dispatcher, $eventDrivenSource) = $this->generateDispatcherAndEventSource();

        /** @var Event[] $acceptedEvents */
        $acceptedEvents = [];
        $dispatcher->addListener(EventType::EVENT_DELETE_ORIGINAL_BEFORE, $this->generateEventsCollector($acceptedEvents));
        $dispatcher->addListener(EventType::EVENT_DELETE_ORIGINAL_AFTER, $this->generateEventsCollector($acceptedEvents));

        $eventDrivenSource->delete('test_phrase');

        $this->assertCount(2, $acceptedEvents);
        $this->assertEquals($acceptedEvents[0]->getEventType()->getId(), EventType::EVENT_DELETE_ORIGINAL_BEFORE);
        $this->assertEquals($acceptedEvents[0]->getEventData()->getOriginal(), 'test_phrase');
        $this->assertEquals($acceptedEvents[1]->getEventType()->getId(), EventType::EVENT_DELETE_ORIGINAL_AFTER);
        $this->assertEquals($acceptedEvents[1]->getEventData()->getOriginal(), 'test_phrase');
    }

    /**
     * @return Array<EventDispatcherInterface|MockObject, EventDrivenSource>
     */
    private function generateDispatcherAndEventSource(): array
    {
        $source = (new SourceFactory())->generateSource(SourceFactory::SOURCE_EVENT_DRIVEN, 'en', true);

        /** @var EventDispatcherInterface|MockObject $dispatcher */
        $dispatcher = new EventDispatcher();
        $eventDrivenSource = new EventDrivenSource($source, $dispatcher);

        return [$dispatcher, $eventDrivenSource];
    }

    private function generateEventsCollector(array &$acceptedEvents): Closure
    {
        return function (Event $event) use (&$acceptedEvents) {
            $acceptedEvents[] = $event;
        };
    }
}
