<?php

namespace ALI\Translator\Source\Sources\EventDriven;

use ALI\Translator\Event\EnumList\EventTimeOrder;
use ALI\Translator\Event\Event;
use ALI\Translator\Event\EventData\DeleteOriginalEventData;
use ALI\Translator\Event\EventData\SaveOriginalEventData;
use ALI\Translator\Event\EventData\SaveTranslateEventData;
use ALI\Translator\Event\EventDispatcherInterface;
use ALI\Translator\PhraseCollection\OriginalPhraseCollection;
use ALI\Translator\Source\Installers\SourceInstallerInterface;
use ALI\Translator\Source\SourceInterface;
use ALI\Translator\Source\SourceWriterInterface;
use InvalidArgumentException;

class EventDrivenSource implements SourceInterface
{
    private SourceWriterInterface $innerWriter;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        SourceWriterInterface    $innerWriter,
        EventDispatcherInterface $dispatcher
    )
    {
        if ($innerWriter === $this) {
            throw new InvalidArgumentException('Inner writer can not be the same as the outer writer');
        }
        $this->innerWriter = $innerWriter;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function saveTranslate(string $languageAlias, string $original, string $translate): void
    {
        $eventData = new SaveTranslateEventData($languageAlias, $original, $translate);

        $this->dispatcher->dispatch(
            new Event(new EventTimeOrder(EventTimeOrder::BEFORE), $eventData)
        );

        $this->innerWriter->saveTranslate($languageAlias, $original, $translate);

        $this->dispatcher->dispatch(
            new Event(new EventTimeOrder(EventTimeOrder::AFTER), $eventData)
        );
    }

    /**
     * @inheritDoc
     */
    public function saveOriginals(array $phrases): void
    {
        $eventDataList = array_map(fn(string $original) => new SaveOriginalEventData($original), $phrases);

        foreach ($eventDataList as $eventData) {
            $this->dispatcher->dispatch(
                new Event(new EventTimeOrder(EventTimeOrder::BEFORE), $eventData)
            );
        }

        $this->innerWriter->saveOriginals($phrases);

        foreach ($eventDataList as $eventData) {
            $this->dispatcher->dispatch(
                new Event(new EventTimeOrder(EventTimeOrder::AFTER), $eventData)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $original): void
    {
        $eventData = new DeleteOriginalEventData($original);

        $this->dispatcher->dispatch(
            new Event(new EventTimeOrder(EventTimeOrder::BEFORE), $eventData)
        );

        $this->innerWriter->delete($original);

        $this->dispatcher->dispatch(
            new Event(new EventTimeOrder(EventTimeOrder::AFTER), $eventData)
        );
    }

    public function getOriginalsIds(array $phrases): array
    {
        return $this->innerWriter->getOriginalsIds($phrases);
    }

    public function getOriginalsByIds(array $originalsIds): array
    {
        return $this->innerWriter->getOriginalsByIds($originalsIds);
    }

    public function getOriginalLanguageAlias(): string
    {
        return $this->innerWriter->getOriginalLanguageAlias();
    }

    public function isSensitiveForRequestsCount(): bool
    {
        return $this->innerWriter->isSensitiveForRequestsCount();
    }

    public function generateInstaller(): SourceInstallerInterface
    {
        return $this->innerWriter->generateInstaller();
    }

    public function getTranslate(string $phrase, string $languageAlias): ?string
    {
        return $this->innerWriter->getTranslate($phrase, $languageAlias);
    }

    public function getTranslates(array $phrases, string $languageAlias): array
    {
        return $this->innerWriter->getTranslates($phrases, $languageAlias);
    }

    public function getAllOriginalTranslates(string $phrase, ?array $languagesAliases = null): array
    {
        return $this->innerWriter->getAllOriginalTranslates($phrase, $languagesAliases);
    }

    public function getExistOriginals(array $phrases): array
    {
        return $this->innerWriter->getExistOriginals($phrases);
    }

    public function getOriginalsWithoutTranslate(string $translationLanguageAlias, int $offset = 0, ?int $limit = null): OriginalPhraseCollection
    {
        return $this->innerWriter->getOriginalsWithoutTranslate($translationLanguageAlias, $offset, $limit);
    }
}

