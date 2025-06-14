<?php

declare(strict_types=1);

namespace Sandstorm\ContentWorkflow\Domain\Workflow\EventStore;

use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event\KonjunkturzyklusWechselExecuted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasCompleted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\TriggeredEreignis;
use Neos\EventStore\Model\Event;
use Neos\EventStore\Model\Event\EventData;
use Neos\EventStore\Model\Event\EventId;
use Neos\EventStore\Model\Event\EventType;
use Neos\EventStore\Model\EventEnvelope;

/**
 * Central authority to convert Game domain events to Event Store EventData and EventType, vice versa.
 *
 * - For normalizing (from classes to event store)
 * - For denormalizing (from event store to classes)
 *
 * @internal inside projections the event will already be denormalized
 */
final readonly class EventNormalizer
{
    private function __construct(
        /**
         * @var array<class-string<WorkflowEventInterface>,EventType>
         */
        private array $fullClassNameToShortEventType,
        /**
         * @var array<string,class-string<WorkflowEventInterface>>
         */
        private array $shortEventTypeToFullClassName,
    ) {
    }

    /**
     * @var array<class-string<WorkflowEventInterface>> $supportedEventClassNames
     * @internal never instantiate this object yourself
     */
    public static function create(array $supportedEventClassNames): self
    {

        $fullClassNameToShortEventType = [];
        $shortEventTypeToFullClassName = [];

        foreach ($supportedEventClassNames as $fullEventClassName) {
            $shortEventClassPosition = strrpos($fullEventClassName, '\\') !== false ? strrpos($fullEventClassName, '\\') : 0;
            $shortEventClassName = substr($fullEventClassName, $shortEventClassPosition + 1);

            $fullClassNameToShortEventType[$fullEventClassName] = EventType::fromString($shortEventClassName);
            $shortEventTypeToFullClassName[$shortEventClassName] = $fullEventClassName;
        }

        return new self(
            fullClassNameToShortEventType: $fullClassNameToShortEventType,
            shortEventTypeToFullClassName: $shortEventTypeToFullClassName
        );
    }

    /**
     * @return class-string<WorkflowEventInterface>
     */
    public function getEventClassName(Event $event): string
    {
        return $this->shortEventTypeToFullClassName[$event->type->value] ?? throw new \InvalidArgumentException(
            sprintf('Failed to denormalize event "%s" of type "%s"', $event->id->value, $event->type->value),
            1651839705
        );
    }

    public function normalize(DecoratedEvent|WorkflowEventInterface $event): Event
    {
        $eventId = $event instanceof DecoratedEvent && $event->eventId !== null ? $event->eventId : EventId::create();
        $eventMetadata = $event instanceof DecoratedEvent ? $event->eventMetadata : null;
        $causationId = $event instanceof DecoratedEvent ? $event->causationId : null;
        $correlationId = $event instanceof DecoratedEvent ? $event->correlationId : null;
        $event = $event instanceof DecoratedEvent ? $event->innerEvent : $event;
        return new Event(
            $eventId,
            $this->getEventType($event),
            $this->getEventData($event),
            $eventMetadata,
            $causationId,
            $correlationId,
        );
    }

    public function denormalize(EventEnvelope $eventEnvelope): WorkflowEventInterface
    {
        $event = $eventEnvelope->event;
        $eventClassName = $this->getEventClassName($event);
        try {
            $eventDataAsArray = json_decode($event->data->value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException(
                sprintf('Failed to decode data of event with type "%s" and id "%s": %s', $event->type->value, $event->id->value, $exception->getMessage()),
                1651839461
            );
        }
        if (!is_array($eventDataAsArray)) {
            throw new \RuntimeException(sprintf('Expected array got %s', $eventDataAsArray));
        }
        /** {@see WorkflowEventInterface::fromArray()} */
        return $eventClassName::fromArray($eventDataAsArray);
    }

    private function getEventData(WorkflowEventInterface $event): EventData
    {
        try {
            $eventDataAsJson = json_encode($event, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Failed to normalize event of type "%s": %s',
                    get_debug_type($event),
                    $exception->getMessage()
                ),
                1651838981
            );
        }
        return EventData::fromString($eventDataAsJson);
    }

    private function getEventType(WorkflowEventInterface $event): EventType
    {
        $className = get_class($event);

        return $this->fullClassNameToShortEventType[$className] ?? throw new \RuntimeException(
            'Event type ' . get_class($event) . ' not registered'
        );
    }
}
